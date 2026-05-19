<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomerResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $token)
    {
        $this->onQueue('high');
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('customers.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject('Restablecer contraseña — Lapsique')
            ->greeting('Hola '.($notifiable->name ?? ''))
            ->line('Recibimos una solicitud para restablecer la contraseña de tu portal de cliente.')
            ->action('Restablecer contraseña', $url)
            ->line('Este enlace expira en 60 minutos.')
            ->line('Si no solicitaste este cambio, ignora este correo.');
    }
}
