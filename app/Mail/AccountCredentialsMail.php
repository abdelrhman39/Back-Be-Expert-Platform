<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<int, array{title: string, price: float|int|string|null}>  $cartItems
     */
    public function __construct(
        public User $user,
        public string $plainPassword,
        public array $cartItems = [],
        public float $cartTotal = 0,
        public string $loginUrl = '',
        public string $checkoutUrl = '',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'تم إنشاء حسابك | '.platform_name('ar'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.account-credentials',
        );
    }
}
