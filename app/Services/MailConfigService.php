<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use App\Repositories\Contracts\SettingRepositoryInterface;

class MailConfigService
{
    /**
     * Constructor
     *
     * @param SettingRepositoryInterface $settingRepository
     *
     * @return void
     */
    public function __construct(
        private readonly SettingRepositoryInterface $settingRepository,
    ) {
    }

    /**
     * Apply mail config
     *
     * @return void
     */
    public function apply(): void
    {
        $mailDetails =
            $this->settingRepository->getSettingArray('mail');

        if (!$mailDetails) {
            return;
        }

        $mailPassword = $mailDetails['mail_password'] ?? null;
        if (!empty($mailPassword)) {
            try {
                $mailPassword = Crypt::decryptString($mailPassword);
            } catch (DecryptException $e) {
                // If not encrypted or plain text fallback
            }
        }

        $mailer = !empty($mailDetails['mail_mailer']) ? $mailDetails['mail_mailer'] : 'smtp';
        $fromAddress = !empty($mailDetails['mail_from_address']) ? $mailDetails['mail_from_address'] : ($mailDetails['mail_username'] ?? 'noreply@example.com');
        $fromName = !empty($mailDetails['mail_from_name']) ? $mailDetails['mail_from_name'] : config('app.name');

        config([
            'mail.default' => $mailer,
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.host' => $mailDetails['mail_host'] ?? '127.0.0.1',
            'mail.mailers.smtp.port' => (int) (!empty($mailDetails['mail_port']) ? $mailDetails['mail_port'] : 587),
            'mail.mailers.smtp.encryption' => !empty($mailDetails['mail_encryption']) ? $mailDetails['mail_encryption'] : 'tls',
            'mail.mailers.smtp.username' => $mailDetails['mail_username'] ?? null,
            'mail.mailers.smtp.password' => $mailPassword,
            'mail.from.address' => $fromAddress,
            'mail.from.name' => $fromName,
        ]);

        // Purge cached mailer so it picks up the updated configuration
        app()->forgetInstance('mailer');
        app()->forgetInstance('mail.manager');
    }
}
