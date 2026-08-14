<?php

namespace App\Services;

use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Support\Str;

class SupportTicketService
{
    public function create(array $data, ?User $user = null): SupportTicket
    {
        $ticket = SupportTicket::query()->create([
            'user_id' => $user?->id,
            'reference_code' => $this->generateReference(),
            'subject' => $data['subject'],
            'category' => $data['category'] ?? 'general',
            'status' => 'open',
            'contact_name' => $data['contact_name'],
            'contact_email' => strtolower(trim($data['contact_email'])),
            'contact_phone' => $data['contact_phone'] ?? null,
            'contact_national_id' => $data['contact_national_id'] ?? null,
        ]);

        SupportMessage::query()->create([
            'ticket_id' => $ticket->id,
            'user_id' => $user?->id,
            'body' => $data['body'],
            'is_staff' => false,
        ]);

        return $ticket->fresh(['messages']);
    }

    public function findByReferenceAndEmail(string $reference, string $email): ?SupportTicket
    {
        return SupportTicket::query()
            ->where('reference_code', strtoupper(trim($reference)))
            ->where('contact_email', strtolower(trim($email)))
            ->first();
    }

    public function grantGuestAccess(SupportTicket $ticket): void
    {
        session()->put($this->sessionKey($ticket), true);
    }

    public function hasGuestAccess(SupportTicket $ticket): bool
    {
        return (bool) session($this->sessionKey($ticket), false);
    }

    public function canView(SupportTicket $ticket, ?User $user = null): bool
    {
        if ($user && $ticket->user_id === $user->id) {
            return true;
        }

        if ($user?->canAdmin('support.view')) {
            return true;
        }

        return $this->hasGuestAccess($ticket);
    }

    public function addReply(SupportTicket $ticket, string $body, ?User $user = null, bool $isStaff = false): SupportMessage
    {
        $message = SupportMessage::query()->create([
            'ticket_id' => $ticket->id,
            'user_id' => $user?->id,
            'body' => $body,
            'is_staff' => $isStaff,
        ]);

        if ($isStaff && in_array($ticket->status, ['open', 'waiting_customer'], true)) {
            $ticket->update(['status' => 'in_progress']);
        }

        if (! $isStaff && $ticket->status === 'waiting_customer') {
            $ticket->update(['status' => 'in_progress']);
        }

        if ($isStaff && filled($ticket->contact_email)) {
            try {
                \Illuminate\Support\Facades\Mail::to($ticket->contact_email)
                    ->send(new \App\Mail\SupportTicketReplyMail($ticket->fresh(), $message));
            } catch (\Throwable) {
                // Mail may be log-only in local env
            }
        }

        return $message;
    }

    public function updateStatus(SupportTicket $ticket, string $status): SupportTicket
    {
        $ticket->update(['status' => $status]);

        return $ticket->fresh();
    }

    protected function generateReference(): string
    {
        do {
            $reference = 'TKT-'.now()->format('ymd').'-'.Str::upper(Str::random(5));
        } while (SupportTicket::query()->where('reference_code', $reference)->exists());

        return $reference;
    }

    protected function sessionKey(SupportTicket $ticket): string
    {
        return 'support_ticket_access.'.$ticket->id;
    }
}
