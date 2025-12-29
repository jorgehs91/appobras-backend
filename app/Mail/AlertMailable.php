<?php

namespace App\Mail;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class AlertMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param  User  $user
     * @param  Collection<int, Task>  $overdueTasks
     * @param  Collection<int, Task>  $nearDueTasks
     * @param  string  $alertType  'overdue' ou 'near_due'
     */
    public function __construct(
        public User $user,
        public Collection $overdueTasks,
        public Collection $nearDueTasks,
        public string $alertType
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = match($this->alertType) {
            'overdue' => __('alerts.overdue.subject'),
            'near_due' => __('alerts.near_due.subject'),
            default => __('alerts.default.subject'),
        };

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $frontendUrl = config('app.frontend_url') ?: config('app.url');
        $tasksUrl = rtrim($frontendUrl, '/') . '/projects';

        return new Content(
            markdown: 'mail.alert',
            with: [
                'user' => $this->user,
                'overdueTasks' => $this->overdueTasks,
                'nearDueTasks' => $this->nearDueTasks,
                'alertType' => $this->alertType,
                'tasksUrl' => $tasksUrl,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
