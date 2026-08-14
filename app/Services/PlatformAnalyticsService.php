<?php

namespace App\Services;

use App\Models\PlatformAnalyticsEvent;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PlatformAnalyticsService
{
    public function dashboard(int $days = 30): array
    {
        $days = in_array($days, [7, 30, 90], true) ? $days : 30;
        $end = now()->endOfDay();
        $start = now()->subDays($days - 1)->startOfDay();
        $previousEnd = $start->copy()->subSecond();
        $previousStart = $previousEnd->copy()->subDays($days - 1)->startOfDay();

        $current = $this->counts($start, $end);
        $previous = $this->counts($previousStart, $previousEnd);
        $series = $this->timeSeries($start, $end);

        return [
            'days' => $days,
            'start' => $start,
            'end' => $end,
            'kpis' => [
                'page_views' => $this->metric($current['page_views'], $previous['page_views']),
                'visits' => $this->metric($current['visits'], $previous['visits']),
                'unique_visitors' => $this->metric($current['unique_visitors'], $previous['unique_visitors']),
                'logins' => $this->metric($current['logins'], $previous['logins']),
                'registrations' => $this->metric($current['registrations'], $previous['registrations']),
                'conversion' => [
                    'value' => $current['unique_visitors'] > 0
                        ? round(($current['registrations'] / $current['unique_visitors']) * 100, 1)
                        : 0,
                    'previous' => $previous['unique_visitors'] > 0
                        ? round(($previous['registrations'] / $previous['unique_visitors']) * 100, 1)
                        : 0,
                    'change' => 0,
                ],
            ],
            'series' => $series,
            'countries' => $this->countries($start, $end)->all(),
            'regions' => $this->locationRows('region', $start, $end)->all(),
            'cities' => $this->locationRows('city', $start, $end)->all(),
            'devices' => $this->dimensionRows('device_type', $start, $end, 6)->all(),
            'browsers' => $this->dimensionRows('browser', $start, $end, 8)->all(),
            'top_pages' => $this->dimensionRows('path', $start, $end, 10)->all(),
            'referrers' => $this->dimensionRows('referrer_host', $start, $end, 10)->all(),
            'today' => $this->counts(now()->startOfDay(), now()->endOfDay()),
        ];
    }

    private function counts(Carbon $start, Carbon $end): array
    {
        $events = PlatformAnalyticsEvent::query()
            ->whereBetween('occurred_at', [$start, $end]);
        $pageViews = (clone $events)->where('event_type', 'page_view');

        return [
            'page_views' => (clone $pageViews)->count(),
            'visits' => (clone $pageViews)->whereNotNull('visit_id')->distinct()->count('visit_id'),
            'unique_visitors' => (clone $pageViews)->whereNotNull('visitor_hash')->distinct()->count('visitor_hash'),
            'logins' => (clone $events)->where('event_type', 'login')->count(),
            'registrations' => User::query()
                ->where('role', 'student')
                ->whereBetween('created_at', [$start, $end])
                ->count(),
        ];
    }

    private function timeSeries(Carbon $start, Carbon $end): array
    {
        $rows = PlatformAnalyticsEvent::query()
            ->select([
                'occurred_on',
                DB::raw("SUM(CASE WHEN event_type = 'page_view' THEN 1 ELSE 0 END) as page_views"),
                DB::raw("SUM(CASE WHEN event_type = 'login' THEN 1 ELSE 0 END) as logins"),
                DB::raw("COUNT(DISTINCT CASE WHEN event_type = 'page_view' THEN visit_id END) as visits"),
            ])
            ->whereBetween('occurred_on', [$start->toDateString(), $end->toDateString()])
            ->groupBy('occurred_on')
            ->get()
            ->keyBy(fn ($row) => Carbon::parse($row->occurred_on)->toDateString());

        $registrations = User::query()
            ->selectRaw('DATE(created_at) as day, COUNT(*) as aggregate')
            ->where('role', 'student')
            ->whereBetween('created_at', [$start, $end])
            ->groupByRaw('DATE(created_at)')
            ->pluck('aggregate', 'day');

        $labels = [];
        $pageViews = [];
        $visits = [];
        $logins = [];
        $registrationSeries = [];

        foreach (CarbonPeriod::create($start->copy()->startOfDay(), $end->copy()->startOfDay()) as $date) {
            $key = $date->toDateString();
            $row = $rows->get($key);
            $labels[] = $date->format('d/m');
            $pageViews[] = (int) ($row?->page_views ?? 0);
            $visits[] = (int) ($row?->visits ?? 0);
            $logins[] = (int) ($row?->logins ?? 0);
            $registrationSeries[] = (int) ($registrations[$key] ?? 0);
        }

        return compact('labels', 'pageViews', 'visits', 'logins') + [
            'registrations' => $registrationSeries,
        ];
    }

    private function countries(Carbon $start, Carbon $end): Collection
    {
        return PlatformAnalyticsEvent::query()
            ->select([
                'country_code',
                'country_name',
                DB::raw('COUNT(*) as views'),
                DB::raw('COUNT(DISTINCT visit_id) as visits'),
            ])
            ->where('event_type', 'page_view')
            ->whereBetween('occurred_at', [$start, $end])
            ->whereNotNull('country_code')
            ->groupBy('country_code', 'country_name')
            ->orderByDesc('visits')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'label' => $row->country_name ?: $row->country_code,
                'code' => $row->country_code,
                'views' => (int) $row->views,
                'visits' => (int) $row->visits,
            ]);
    }

    private function locationRows(string $column, Carbon $start, Carbon $end): Collection
    {
        return PlatformAnalyticsEvent::query()
            ->select([
                $column,
                DB::raw('COUNT(*) as views'),
                DB::raw('COUNT(DISTINCT visit_id) as visits'),
            ])
            ->where('event_type', 'page_view')
            ->whereBetween('occurred_at', [$start, $end])
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->groupBy($column)
            ->orderByDesc('visits')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'label' => $row->{$column},
                'views' => (int) $row->views,
                'visits' => (int) $row->visits,
            ]);
    }

    private function dimensionRows(string $column, Carbon $start, Carbon $end, int $limit): Collection
    {
        return PlatformAnalyticsEvent::query()
            ->select([
                $column,
                DB::raw('COUNT(*) as views'),
                DB::raw('COUNT(DISTINCT visit_id) as visits'),
            ])
            ->where('event_type', 'page_view')
            ->whereBetween('occurred_at', [$start, $end])
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->groupBy($column)
            ->orderByDesc('views')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'label' => $row->{$column},
                'views' => (int) $row->views,
                'visits' => (int) $row->visits,
            ]);
    }

    private function metric(int $value, int $previous): array
    {
        return [
            'value' => $value,
            'previous' => $previous,
            'change' => $previous > 0
                ? round((($value - $previous) / $previous) * 100, 1)
                : ($value > 0 ? 100 : 0),
        ];
    }
}
