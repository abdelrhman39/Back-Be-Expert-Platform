<?php

namespace App\Mail;

use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportTicketReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public SupportTicket $ticket,
        public SupportMessage $message,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'رد على تذكرتك '.$this->ticket->reference_code.' | منصة مركز التعلم المستمر',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.support-ticket-reply',
        );
    }
}
