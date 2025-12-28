<?php

namespace App\Mail;

use App\Models\Project;
use App\Models\ProjectInvite;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProjectInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public ProjectInvite $invite,
        public Project $project,
        public User $user
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Convite para Projeto: ' . $this->project->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // URL do frontend para aceitar convite
        // O frontend deve ter uma rota que chama POST /api/v1/invites/project/{token}/accept
        $frontendUrl = config('app.frontend_url') ?: config('app.url');
        $acceptUrl = rtrim($frontendUrl, '/') . '/invites/project/' . $this->invite->token;

        return new Content(
            markdown: 'mail.project-invitation',
            with: [
                'projectName' => $this->project->name,
                'role' => $this->invite->role,
                'acceptUrl' => $acceptUrl,
                'expiresAt' => $this->invite->expires_at?->format('d/m/Y H:i'),
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
