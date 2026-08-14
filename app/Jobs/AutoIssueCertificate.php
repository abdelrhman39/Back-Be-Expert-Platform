<?php

namespace App\Jobs;

use App\Services\AutomaticCertificateIssuanceService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class AutoIssueCertificate implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $uniqueFor = 300;

    /** @var array<int, int> */
    public array $backoff = [30, 120, 300];

    public function __construct(
        public readonly int $studentId,
        public readonly string $trigger = 'automatic',
    ) {}

    public function uniqueId(): string
    {
        return (string) $this->studentId;
    }

    public function handle(AutomaticCertificateIssuanceService $service): void
    {
        $service->issueForStudentId($this->studentId, $this->trigger);
    }
}
