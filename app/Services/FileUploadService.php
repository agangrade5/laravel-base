<?php

namespace App\Services;

use App\Exceptions\FileUploadException;
use App\Repositories\Contracts\SettingRepositoryInterface;
use Aws\Exception\AwsException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;
use Throwable;

class FileUploadService
{
    /**
     * Cached S3 disk, so the settings table is not queried repeatedly
     * within the same request.
     */
    private ?Filesystem $s3Disk = null;

    public function __construct(
        private readonly SettingRepositoryInterface $settingRepository
    ) {
    }

    /**
     * Resolve the filesystem.
     *
     * - FILESYSTEM_DISK=s3  => credentials are loaded from the settings table (aws)
     * - otherwise           => the regular configured disk (public/local)
     */
    public function disk(?string $disk = null): Filesystem
    {
        // Default disk
        $disk = $disk ?? config('filesystems.default');

        // S3 disk
        if ($disk === 's3') {
            return $this->s3Disk ??= $this->buildS3Disk();
        }

        return Storage::disk($disk);
    }

    /**
     * Build the S3 disk at runtime from the settings table.
     *
     * @throws FileUploadException
     */
    private function buildS3Disk(): Filesystem
    {
        // getAllSettingsFormatted() decrypts the secret and returns it
        // under the 'aws_secret_access_key_decrypted' key.
        $aws = $this->settingRepository->getAllSettingsFormatted()['aws'] ?? [];

        $key    = $aws['aws_access_key_id'] ?? null;
        $secret = $aws['aws_secret_access_key_decrypted'] ?? null;
        $region = $aws['aws_default_region'] ?? null;
        $bucket = $aws['aws_bucket'] ?? null;

        // AWS settings are incomplete
        if (!$key || !$secret || !$region || !$bucket) {
            throw new FileUploadException(
                'AWS settings are incomplete. Please check the access key, secret, region and bucket in Settings.'
            );
        }

        return Storage::build([
            'driver'                  => 's3',
            'key'                     => $key,
            'secret'                  => $secret,
            'region'                  => $region,
            'bucket'                  => $bucket,
            'url'                     => $aws['aws_url'] ?? null,
            'endpoint'                => $aws['aws_endpoint'] ?? null,
            'use_path_style_endpoint' => (bool) ($aws['aws_use_path_style_endpoint'] ?? false),
            'throw'                   => true,
        ]);
    }

    /**
     * Upload and optimize an image (converted to WEBP).
     *
     * Options: disk, width, height, quality, filename
     *
     * @return string Stored path (save this in the database)
     *
     * @throws FileUploadException
     */
    public function uploadImage(
        UploadedFile $file,
        string $directory,
        array $options = []
    ): string {
        // Defaults
        $width   = $options['width'] ?? null;
        $height  = $options['height'] ?? null;
        $quality = $options['quality'] ?? 85;

        // Decode the image
        $image = ImageManager::usingDriver(Driver::class)
            ->decode($file->getRealPath());

        if ($width && $height) {
            // Both given: crop to exact size
            $image->cover($width, $height);
        } elseif ($width || $height) {
            // Only one given: keep aspect ratio, never upscale
            $image->scaleDown($width, $height);
        }

        // Convert to WEBP
        $data = $image
            ->encodeUsingFormat(Format::WEBP, quality: $quality)
            ->toString();

        // Generate filename
        $filename = $options['filename'] ?? (Str::uuid() . '.webp');
        $path     = trim($directory, '/') . '/' . $filename;

        // Upload the image
        $this->guard(
            fn () => $this->disk($options['disk'] ?? null)->put($path, $data),
            'upload'
        );

        return $path;
    }

    /**
     * Upload a regular file (pdf, doc, etc.) without processing.
     *
     * @throws FileUploadException
     */
    public function uploadFile(
        UploadedFile $file,
        string $directory,
        ?string $disk = null
    ): string {
        return $this->guard(
            fn () => $this->disk($disk)->putFileAs(
                trim($directory, '/'),
                $file,
                Str::uuid() . '.' . $file->getClientOriginalExtension()
            ),
            'upload'
        );
    }

    /**
     * Delete a file (safe to call with an empty path).
     *
     * @throws FileUploadException
     */
    public function delete(?string $path, ?string $disk = null): bool
    {
        // Safe to call with an empty path
        if (empty($path)) {
            return false;
        }

        return $this->guard(fn () => $this->disk($disk)->delete($path), 'delete');
    }

    /**
     * Upload a new image and delete the old one.
     *
     * @throws FileUploadException
     */
    public function replaceImage(
        ?string $oldPath,
        UploadedFile $file,
        string $directory,
        array $options = []
    ): string {
        // Upload the new image first, so the old one stays safe if this fails.
        $newPath = $this->uploadImage($file, $directory, $options);

        // Deleting the old image is best-effort; do not fail the upload because of it.
        try {
            $this->delete($oldPath, $options['disk'] ?? null);
        } catch (FileUploadException $e) {
            // Already logged inside guard()
        }

        return $newPath;
    }

    /**
     * Public URL of a stored file.
     *
     * @throws FileUploadException
     */
    public function url(?string $path, ?string $disk = null): ?string
    {
        // Safe to call with an empty path
        if (empty($path)) {
            return null;
        }

        return $this->guard(fn () => $this->disk($disk)->url($path), 'url');
    }

    /**
     * Temporary URL for private buckets.
     *
     * @throws FileUploadException
     */
    public function temporaryUrl(?string $path, int $minutes = 30, ?string $disk = null): ?string
    {
        // Safe to call with an empty path
        if (empty($path)) {
            return null;
        }

        return $this->guard(
            fn () => $this->disk($disk)->temporaryUrl($path, now()->addMinutes($minutes)),
            'url'
        );
    }

    /**
     * Run any storage operation through this wrapper. On failure the real
     * error is logged and the user receives a friendly message.
     *
     * @throws FileUploadException
     */
    private function guard(callable $callback, string $action): mixed
    {
        try {
            return $callback();
        } catch (FileUploadException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error("FileUpload [{$action}] failed: " . $e->getMessage(), [
                'exception' => $e,
            ]);

            throw new FileUploadException($this->friendlyMessage($e), 0, $e);
        }
    }

    /**
     * Convert an AWS error code into a human-readable message.
     */
    private function friendlyMessage(Throwable $e): string
    {
        $code = null;

        // Walk the exception chain to find the underlying AWS exception
        for ($t = $e; $t; $t = $t->getPrevious()) {
            if ($t instanceof AwsException) {
                $code = $t->getAwsErrorCode();
                break;
            }
        }

        return match ($code) {
            'InvalidAccessKeyId', 'SignatureDoesNotMatch', 'InvalidClientTokenId', 'ExpiredToken'
                => 'AWS credentials are invalid. Please check the Access Key and Secret in Settings.',
            'AccessDenied', 'AllAccessDisabled'
                => 'The AWS user does not have permission to access the bucket (PutObject/DeleteObject/GetObject).',
            'NoSuchBucket'
                => 'AWS bucket not found. Please check the bucket name.',
            'AuthorizationHeaderMalformed', 'PermanentRedirect', 'IllegalLocationConstraintException'
                => 'AWS region is incorrect. Please enter the bucket\'s correct region in Settings.',
            default
                => 'There was a problem with file storage. Please check your AWS settings or try again later.',
        };
    }
}
