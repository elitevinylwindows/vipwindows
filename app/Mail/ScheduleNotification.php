<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ScheduleNotification extends Mailable
{
    use Queueable, SerializesModels;

    public string $eventTitle;
    public string $eventDate;
    public ?string $startTime;
    public ?string $endTime;
    public ?string $address;
    public ?string $description;
    public string $customerName;
    public string $type; // 'event', 'job'

    public function __construct(array $data)
    {
        $this->eventTitle   = $data['title'] ?? 'Scheduled Appointment';
        $this->eventDate    = $data['event_date'] ?? '';
        $this->startTime    = $data['start_time'] ?? null;
        $this->endTime      = $data['end_time'] ?? null;
        $this->address      = $data['address'] ?? null;
        $this->description  = $data['description'] ?? null;
        $this->customerName = $data['customer_name'] ?? 'Customer';
        $this->type         = $data['type'] ?? 'event';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'VIP Windows — ' . $this->eventTitle,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.schedule-notification',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
