<?php

namespace App\Support\Reports;

use Carbon\Carbon;
use Carbon\CarbonInterface;

class ReportFilter
{
    public function __construct(
        public readonly CarbonInterface $from,
        public readonly CarbonInterface $to,
        public readonly string $preset = '30d',
        public readonly ?int $programId = null,
        public readonly ?int $batchId = null,
        public readonly ?string $status = null,
    ) {}

    public static function fromInputs(
        string $preset = '30d',
        ?string $from = null,
        ?string $to = null,
        ?int $programId = null,
        ?int $batchId = null,
        ?string $status = null,
    ): self {
        $preset = array_key_exists($preset, config('admin-reports.presets', []))
            ? $preset
            : '30d';

        if ($preset === 'month') {
            return new self(
                from: now()->startOfMonth()->startOfDay(),
                to: now()->endOfDay(),
                preset: $preset,
                programId: $programId ?: null,
                batchId: $batchId ?: null,
                status: filled($status) ? $status : null,
            );
        }

        if ($preset === 'custom') {
            $start = filled($from)
                ? Carbon::parse($from)->startOfDay()
                : now()->subDays(29)->startOfDay();
            $end = filled($to)
                ? Carbon::parse($to)->endOfDay()
                : now()->endOfDay();

            if ($start->greaterThan($end)) {
                [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
            }

            return new self(
                from: $start,
                to: $end,
                preset: $preset,
                programId: $programId ?: null,
                batchId: $batchId ?: null,
                status: filled($status) ? $status : null,
            );
        }

        $days = (int) (config("admin-reports.presets.{$preset}.days") ?? 30);

        return new self(
            from: now()->subDays(max(1, $days) - 1)->startOfDay(),
            to: now()->endOfDay(),
            preset: $preset,
            programId: $programId ?: null,
            batchId: $batchId ?: null,
            status: filled($status) ? $status : null,
        );
    }

    public function label(): string
    {
        return $this->from->format('Y-m-d').' → '.$this->to->format('Y-m-d');
    }

    public function daysSpan(): int
    {
        return max(1, $this->from->diffInDays($this->to) + 1);
    }
}
