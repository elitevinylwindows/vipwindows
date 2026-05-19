<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class JobNotification extends Mailable
{
    use Queueable, SerializesModels;

    public string $emailSubject;
    public string $emailBody;
    public string $customerName;

    public function __construct(string $subject, string $body, string $customerName)
    {
        $this->emailSubject = $subject;
        $this->emailBody = $body;
        $this->customerName = $customerName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.job-notification',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
