<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendNotification extends Notification
{
    use Queueable;

    private $customData;

    /**
     * Create a new notification instance.
     *
     * @param mixed $customData Pass either a structured array or a simple message string
     * @return void
     */
    public function __construct($customData)
    {
        $this->customData = $customData;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification saved in the DB.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        // If it's a simple string message from NotificationService, map it safely
        if (is_string($this->customData)) {
            return [
                'sender_id'     => auth()->id() ?? null,
                'receiver_id'   => $notifiable->id ?? null,
                'reminder_date' => date('Y-m-d'),
                'document_name' => null,
                'message'       => $this->customData
            ];
        }

        // Otherwise handle structured array payloads safely
        return [
            'sender_id'     => $this->customData['sender_id'] ?? (auth()->id() ?? null),
            'receiver_id'   => $this->customData['receiver_id'] ?? ($notifiable->id ?? null),
            'reminder_date' => isset($this->customData['reminder_date']) ? date('Y-m-d', strtotime($this->customData['reminder_date'])) : date('Y-m-d'),
            'document_name' => $this->customData['document_name'] ?? null,
            'message'       => $this->customData['message'] ?? ''
        ];
    }
}
