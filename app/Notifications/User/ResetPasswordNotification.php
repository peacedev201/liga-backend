<?php

namespace App\Notifications\User;

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
            ->subject('Passwort ändern')
            ->greeting('Hallo!')
            ->line('Du erhälst diese E-Mail da ein neues Passwort angefordert wurde. Nutze folgenden Button um ein neues Passwort festzulegen:')
            ->action('Neues Passwort festlegen', "https://efootball.liga.sh/reset/{$this->token}")
            ->line('Solltest du kein neues Passwort angefordert haben, ignoriere bitte diese E-Mail.');
    }
}
