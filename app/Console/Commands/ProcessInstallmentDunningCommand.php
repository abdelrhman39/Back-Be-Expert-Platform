<?php

namespace App\Console\Commands;

use App\Services\InstallmentDunningService;
use App\Support\InstallmentSettings;
use Illuminate\Console\Command;

class ProcessInstallmentDunningCommand extends Command
{
    protected $signature = 'installments:process-dunning';

    protected $description = 'تشغيل خطوات تصعيد متأخرات الأقساط الديناميكية';

    public function handle(InstallmentDunningService $dunning): int
    {
        if (! InstallmentSettings::dunningEnabled()) {
            $this->info('مسار التصعيد متوقف من الإعدادات.');

            return self::SUCCESS;
        }

        $result = $dunning->process();

        $this->info(sprintf(
            'تمت معالجة %d قسطاً — نُفّذت %d خطوة — تُخطّيت %d.',
            $result['processed'],
            $result['executed'],
            $result['skipped'],
        ));

        return self::SUCCESS;
    }
}
