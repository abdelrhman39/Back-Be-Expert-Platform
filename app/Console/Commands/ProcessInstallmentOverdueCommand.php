<?php

namespace App\Console\Commands;

use App\Services\InstallmentOverdueService;
use Illuminate\Console\Command;

class ProcessInstallmentOverdueCommand extends Command
{
    protected $signature = 'installments:process-overdue';

    protected $description = 'معالجة المتأخرات وإيقاف الالتحاق عند التأخر';

    public function handle(InstallmentOverdueService $service): int
    {
        $result = $service->process();
        $this->info(sprintf(
            'متأخرات: %d إشعار | إيقاف: %d | استعادة: %d | رسوم تأخير: %d',
            $result['overdue_notified'],
            $result['suspended'],
            $result['restored'],
            $result['late_fees'] ?? 0,
        ));

        return self::SUCCESS;
    }
}
