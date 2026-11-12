<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountCreatedNotification extends Notification
{
    use Queueable;

    public $password;
    public $role;

    public function __construct(string $password, string $role)
    {
        $this->password = $password;
        $this->role = $role;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Akun PettyClaim Anda Telah Dibuat')
            ->view('emails.account_created', [
                'name' => $notifiable->name,
                'email' => $notifiable->email,
                'password' => $this->password,
                'role' => ucfirst($this->role),
            ]);
    }
}