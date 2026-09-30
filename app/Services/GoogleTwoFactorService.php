<?php

namespace App\Services;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use PragmaRX\Google2FA\Google2FA;

/**
 * Thin wrapper around pragmarx/google2fa so the rest of the app
 * (controllers) never talks to the TOTP / QR libraries directly.
 *
 * composer require pragmarx/google2fa bacon/bacon-qr-code
 */
class GoogleTwoFactorService
{
    /**
     * Constructor
     *
     * @param Google2FA $google2fa
     *
     * @return void
     */
    public function __construct(
        private Google2FA $google2fa
    )
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Generate a secret key
     *
     * @return string
     */
    public function generateSecretKey(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    /**
     * Get a QR code SVG string
     *
     * @param string $companyName
     * @param string $email
     * @param string $secret
     *
     * @return string
     */
    public function getQrCodeSvg(string $companyName, string $email, string $secret): string
    {
        $otpAuthUrl = $this->google2fa->getQRCodeUrl($companyName, $email, $secret);

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);

        return $writer->writeString($otpAuthUrl);
    }

    /**
     * Verify a one-time password
     *
     * @param string $secret
     * @param string $oneTimePassword
     *
     * @return bool
     */
    public function verifyKey(string $secret, string $oneTimePassword): bool
    {
        return $this->google2fa->verifyKey($secret, $oneTimePassword, 1);
    }
}
