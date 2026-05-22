<?php

namespace App\Notifications;

use App\Mail\CustomerPasswordResetEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
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

    public function toMail(object $notifiable): CustomerPasswordResetEmail
    {
        $url = route('customers.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new CustomerPasswordResetEmail($notifiable, $this->token, $url))
            ->to($notifiable->getEmailForPasswordReset());
    }
}
