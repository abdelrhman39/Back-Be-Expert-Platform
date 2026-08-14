<?php

use App\Models\ExamAttempt;
use App\Models\PlatformAnalyticsEvent;
use App\Services\AutomaticCertificateIssuanceService;
use App\Services\ExamAttemptService;
use App\Support\CertificateAccessSettings;
use App\Support\InstallmentSettings;
use App\Support\ZoomSettings;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('exams:submit-expired', function () {
    $count = 0;

    ExamAttempt::query()
        ->where('status', 'in_progress')
        ->whereNotNull('expires_at')
        ->where('expires_at', '<=', now())
        ->orderBy('id')
        ->chunkById(100, function ($attempts) use (&$count) {
            foreach ($attempts as $attempt) {
                app(ExamAttemptService::class)->submit($attempt, 'time_expired');
                $count++;
            }
        });

    $this->info("Submitted {$count} expired exam attempt(s).");
})->purpose('Submit timed-out exam attempts safely');

Artisan::command('certificates:auto-issue', function () {
    $count = app(AutomaticCertificateIssuanceService::class)->processEligible();
    $this->info("Issued {$count} certificate(s) automatically.");
})->purpose('Issue certificates for all students who meet the global policy');

Artisan::command('analytics:prune', function () {
    $days = max(30, (int) config('analytics.retention_days', 180));
    $deleted = PlatformAnalyticsEvent::query()
        ->where('occurred_at', '<', now()->subDays($days))
        ->delete();

    $this->info("Deleted {$deleted} analytics event(s) older than {$days} days.");
})->purpose('Delete expired first-party analytics events');

Schedule::command('teams:sync-attendance')->everyFifteenMinutes();
Schedule::command('teams:sync-recordings')->everyThirtyMinutes();
Schedule::command('zoom:sync-attendance')
    ->everyFiveMinutes()
    ->when(fn () => ZoomSettings::enabled())
    ->withoutOverlapping();
Schedule::command('zoom:sync-recordings')
    ->everyFifteenMinutes()
    ->when(fn () => ZoomSettings::enabled())
    ->withoutOverlapping();
Schedule::command('notifications:dispatch-lecture-reminders')->everyFiveMinutes();
Schedule::command('installments:dispatch-reminders')
    ->dailyAt(InstallmentSettings::reminderDispatchTime())
    ->when(fn () => InstallmentSettings::remindersEnabled());
Schedule::command('installments:process-overdue')
    ->dailyAt(InstallmentSettings::overdueProcessTime())
    ->when(fn () => InstallmentSettings::suspensionEnabled());
Schedule::command('installments:process-dunning')
    ->dailyAt(InstallmentSettings::dunningProcessTime())
    ->when(fn () => InstallmentSettings::dunningEnabled())
    ->withoutOverlapping();
Schedule::command('sessions:generate-upcoming')->weeklyOn(0, '06:00');
Schedule::command('exams:submit-expired')
    ->everyMinute()
    ->withoutOverlapping();
Schedule::command('certificates:auto-issue')
    ->everyTenMinutes()
    ->when(fn () => CertificateAccessSettings::autoIssueEnabled())
    ->withoutOverlapping();
Schedule::command('analytics:prune')->dailyAt('03:30')->withoutOverlapping();
