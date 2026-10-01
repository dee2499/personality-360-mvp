<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmployeeInvitationNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $token,
        public ?string $companyName = null
    ) {}

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
        $company = $this->companyName ?? $notifiable->company?->name ?? 'your organization';
        $activationUrl = route('invitation.accept', ['token' => $this->token]);

        return (new MailMessage)
            ->subject("You've been invited to {$company} on Personality 360")
            ->greeting("Hello {$notifiable->name}!")
            ->line("You have been added as an employee of {$company} on Personality 360 Assessment.")
            ->line('Please click the button below to set your password and activate your account so you can participate in 360 feedback surveys.')
            ->action('Set Password & Activate Account', $activationUrl)
            ->line('If you did not expect this invitation, you can safely ignore this email.');
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
