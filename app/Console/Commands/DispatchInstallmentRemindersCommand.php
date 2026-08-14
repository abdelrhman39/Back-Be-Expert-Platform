<?php

namespace App\Console\Commands;

use App\Services\InstallmentReminderService;
use Illuminate\Console\Command;

class DispatchInstallmentRemindersCommand extends Command
{
    protected $signature = 'installments:dispatch-reminders';

    protected $description = 'إرسال تذكيرات الأقساط قبل الاستحقاق (7 / 3 / 1 أيام)';

    public function handle(InstallmentReminderService $service): int
    {
        $sent = $service->dispatch();
        $this->info("تم إرسال {$sent} تذكيراً.");

        return self::SUCCESS;
    }
}
