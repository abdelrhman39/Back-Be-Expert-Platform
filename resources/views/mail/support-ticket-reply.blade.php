<x-mail::message>
# رد جديد على تذكرتك

**الموضوع:** {{ $ticket->subject }}

**رقم التذكرة:** {{ $ticket->reference_code }}

---

{!! nl2br(e($message->body)) !!}

<x-mail::button :url="route('support.ticket.view', ['locale' => 'ar', 'ticket' => $ticket->reference_code])">
متابعة التذكرة
</x-mail::button>

شكراً,<br>
{{ config('app.name') }}
</x-mail::message>
