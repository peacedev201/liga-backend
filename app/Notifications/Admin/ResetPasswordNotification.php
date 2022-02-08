<?php

namespace App\Notifications\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    use Queueable;

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Reset Password')
            ->greeting('Hello!')
            ->line('You are receiving this email because we received a password reset request for your account.Click the button below to reset your password:')
            ->action('Reset Password', config('app.admin_client_url') . "/reset/{$this->token}?email=" . urlencode($notifiable->getEmailForPasswordReset()))
            ->line('If you did not request a password reset, no further action is required.');
    }
}
