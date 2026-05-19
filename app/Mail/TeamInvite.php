<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeamInvite extends Mailable
{
    use Queueable, SerializesModels;

    public string $memberName;
    public string $memberEmail;
    public string $memberRole;
    public string $plainPassword;
    public string $loginUrl;
    public string $invitedBy;

    public function __construct(array $data)
    {
        $this->memberName    = $data['name'];
        $this->memberEmail   = $data['email'];
        $this->memberRole    = $data['role'];
        $this->plainPassword = $data['password'];
        $this->loginUrl      = $data['login_url'] ?? url('/admin');
        $this->invitedBy     = $data['invited_by'] ?? 'VIP Windows';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You\'re Invited to VIP Windows — Account Created',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.team-invite',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
