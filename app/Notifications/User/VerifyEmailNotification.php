<?php

namespace App\Notifications\User;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class VerifyEmailNotification extends Notification
{
    /**
     * The callback that should be used to build the mail message.
     *
     * @var \Closure|null
     */
    public static $toMailCallback;

    /**
     * Get the notification's channels.
     *
     * @param  mixed  $notifiable
     * @return array|string
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(Lang::get('Bitte verifiziere deine E-Mail Adresse'))
            ->line(Lang::get('Bitte nutze den folgenden Button um deine E-Mail zu verifizieren.'))
            ->action(Lang::get('Bitte verifiziere deine E-Mail Adresse'), "https://efootball.liga.sh/verify?key=" . $notifiable->getKey() . "&secret=" . sha1($notifiable->getEmailForVerification()))
            ->line(Lang::get('Solltest du keinen Account bei uns erstellt haben, ignoriere diese E-Mail.'));
    }
}
