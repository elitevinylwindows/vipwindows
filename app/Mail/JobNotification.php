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
    protected ?string $pdfPath;

    public function __construct(string $subject, string $body, string $customerName, ?string $pdfPath = null)
    {
        $this->emailSubject = $subject;
        $this->emailBody = $body;
        $this->customerName = $customerName;
        $this->pdfPath = $pdfPath;
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
        if ($this->pdfPath && \Storage::disk('public')->exists($this->pdfPath)) {
            return [
                \Illuminate\Mail\Mailables\Attachment::fromStorageDisk('public', $this->pdfPath)
                    ->as('job-documents.pdf')
                    ->withMime('application/pdf'),
            ];
        }

        return [];
    }
}
