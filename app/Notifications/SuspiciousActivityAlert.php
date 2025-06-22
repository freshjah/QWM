<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class SuspiciousActivityAlert extends Notification
{
    use Queueable;

    public function __construct(public Collection $entries) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $msg = new MailMessage;
        $msg->subject('Suspicious Verification Activity Detected')
            ->line('We detected multiple failed verification attempts from the following IPs:');

        foreach ($this->entries as $ip => $attempts) {
            $msg->line("$ip: {$attempts->count()} failures");
        }

        return $msg->line('Please investigate via the admin dashboard.');
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
