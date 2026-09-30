<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\CarbonInterval;

class CustomResetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param string $token
     *
     * @return void
     */
    public function __construct(
        protected string $token
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
        $url = url(
            route('admin.password.reset', [
                'token' => $this->token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false)
        );

        $expireMinutes = (int) config('auth.passwords.users.expire', 60);
        $expireText = CarbonInterval::minutes($expireMinutes)->cascade()->forHumans();

        return (new MailMessage)
            ->subject('Reset Your Password - ' . config('app.name'))
            ->view('emails.auth.password-reset', [
                'user' => $notifiable,
                'url' => $url,
                'expireText' => $expireText,
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
