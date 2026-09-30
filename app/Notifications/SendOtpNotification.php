<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendOtpNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param string $otp
     * @param string $otpExpireTime
     *
     * @return void
     */
    public function __construct(
        protected string $otp,
        protected string $otpExpireTime
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Login OTP - ' . config('app.name'))
            ->view('emails.auth.login-otp', [
                'user' => $notifiable,
                'otp' => $this->otp,
                'otpExpireTime' => $this->otpExpireTime,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
