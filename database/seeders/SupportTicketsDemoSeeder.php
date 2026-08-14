<?php

namespace Database\Seeders;

use App\Models\SupportTicket;
use App\Models\User;
use App\Services\SupportTicketService;
use Illuminate\Database\Seeder;

class SupportTicketsDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (SupportTicket::query()->exists()) {
            return;
        }

        $user = User::query()->where('email', 'demo@domain.test')->first();
        $service = app(SupportTicketService::class);

        $service->create([
            'subject' => 'استفسار عن محتوى دورة ITIL',
            'category' => 'course',
            'contact_name' => $user?->displayName() ?? 'طالب تجريبي',
            'contact_email' => $user?->email ?? 'demo@domain.test',
            'contact_phone' => $user?->phone,
            'contact_national_id' => $user?->national_id,
            'body' => 'مرحباً، لا أستطيع الوصول لمحتوى الوحدة الثانية في دورة ITIL. يرجى المساعدة.',
        ], $user);

        $ticket = SupportTicket::query()->first();
        if ($ticket) {
            $service->addReply($ticket, 'شكراً لتواصلك. جاري مراجعة تسجيلك في الدورة وسنرد خلال 24 ساعة.', null, true);
            $service->updateStatus($ticket, 'in_progress');
        }
    }
}
