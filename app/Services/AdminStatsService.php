<?php

namespace App\Services;

use App\Models\AcademicBatch;
use App\Models\AcademicProgram;
use App\Models\AcademicStaff;
use App\Models\AcademicStudent;
use App\Models\CatalogCourse;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdminStatsService
{
    /** @return array<string, int|float|string> */
    public function dashboard(): array
    {
        $ordersTotal = (float) Order::query()->sum('total');
        $ordersPaid = (float) Order::query()->where('status', 'paid')->sum('total');
        $ordersPending = Order::query()->where('status', 'pending_payment')->count();

        return [
            'students_total' => AcademicStudent::query()->count(),
            'students_active' => AcademicStudent::query()->where('academic_status', 'studying')->count(),
            'users_total' => User::query()->count(),
            'catalog_courses' => CatalogCourse::query()->count(),
            'programs_active' => AcademicProgram::query()->where('status', 'active')->count(),
            'batches_active' => AcademicBatch::query()->count(),
            'orders_total' => Order::query()->count(),
            'orders_pending' => $ordersPending,
            'revenue_total' => $ordersTotal,
            'revenue_collected' => $ordersPaid,
        ];
    }

    /** @return array<string, mixed> */
    public function financial(): array
    {
        $stats = $this->dashboard();

        $byMethod = Order::query()
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
            ->groupBy('payment_method')
            ->get();

        $pendingTotal = (float) Order::query()->where('status', 'pending_payment')->sum('total');
        $paidCount = Order::query()->where('status', 'paid')->count();

        return array_merge($stats, [
            'payment_methods' => $byMethod,
            'unique_buyers' => Order::query()->distinct('user_id')->count('user_id'),
            'revenue_pending' => $pendingTotal,
            'orders_paid_count' => $paidCount,
        ]);
    }

    /** @return array<string, mixed> */
    public function enrollment(): array
    {
        $total = AcademicStudent::query()->count();
        $studying = AcademicStudent::query()->where('academic_status', 'studying')->count();
        $pending = AcademicStudent::query()->where('academic_status', 'pending')->count();
        $withdrawn = AcademicStudent::query()->where('academic_status', 'withdrawn')->count();
        $deferred = AcademicStudent::query()->where('academic_status', 'deferred')->count();
        $suspended = AcademicStudent::query()->where('academic_status', 'suspended')->count();

        $female = AcademicStudent::query()->where('gender', 'أنثى')->count();
        $male = AcademicStudent::query()->where('gender', 'ذكر')->count();
        $genderTotal = max(1, $female + $male);

        $revenue = (float) Order::query()->where('status', 'paid')->sum('total');

        return [
            'total_enrolled' => $total,
            'confirmed' => $studying,
            'unconfirmed' => $pending,
            'revenue_paid' => $revenue,
            'active_trainees' => $studying,
            'active_programs' => AcademicProgram::query()->where('status', 'active')->count(),
            'studying' => $studying,
            'pending_registration' => $pending,
            'withdrawn' => $withdrawn,
            'deferred' => $deferred,
            'suspended' => $suspended,
            'female_count' => $female,
            'male_count' => $male,
            'female_pct' => round(($female / $genderTotal) * 100),
            'male_pct' => round(($male / $genderTotal) * 100),
            'top_programs' => $this->topProgramsByEnrollment(),
            'recent_students' => AcademicStudent::query()->with('batch.program')->latest()->limit(8)->get(),
        ];
    }

    /** @return array<string, mixed> */
    public function graduates(): array
    {
        $graduated = AcademicStudent::query()->where('academic_status', 'graduated')->count();
        $eligible = AcademicStudent::query()->where('academic_status', 'eligible')->count();
        $expected = AcademicStudent::query()->where('academic_status', 'expected')->count();

        return [
            'graduated_total' => $graduated,
            'expected_graduation' => $expected,
            'eligible_graduation' => $eligible,
            'certificates_issued' => $graduated,
            'review_pct' => $graduated > 0 ? 100 : 0,
            'approval_pct' => $graduated > 0 ? 100 : 0,
            'issue_pct' => $graduated > 0 ? 100 : 0,
            'graduates_list' => AcademicStudent::query()
                ->where('academic_status', 'graduated')
                ->with('batch.program')
                ->latest('graduated_at')
                ->limit(10)
                ->get(),
        ];
    }

    /** @return array<string, mixed> */
    public function staff(): array
    {
        $staff = AcademicStaff::query()
            ->with(['user', 'schedules.section.program'])
            ->get();
        $totalCompensation = (float) $staff->sum('compensation_total');
        $totalHours = (int) $staff->sum('hours_per_week');
        $activeCount = $staff->where('status', 'active')->count();

        $male = $staff->where('gender', 'ذكر')->count();
        $female = $staff->where('gender', 'أنثى')->count();
        $genderTotal = max(1, $male + $female);

        return [
            'staff_total' => $staff->count(),
            'staff_active' => $activeCount,
            'portal_accounts' => $staff->whereNotNull('user_id')->count(),
            'portal_ready' => $staff->filter(fn (AcademicStaff $member) => $member->canBeImpersonated())->count(),
            'unassigned' => $staff->filter(fn (AcademicStaff $member) => $member->schedules->isEmpty())->count(),
            'compensation_total' => $totalCompensation,
            'hours_total' => $totalHours,
            'avg_courses' => $staff->count() ? round($staff->avg('courses_count'), 2) : 0,
            'avg_hours' => $staff->count() ? round($staff->avg('hours_per_week'), 2) : 0,
            'male_count' => $male,
            'female_count' => $female,
            'male_pct' => round(($male / $genderTotal) * 100),
            'female_pct' => round(($female / $genderTotal) * 100),
            'staff_list' => $staff->sortByDesc('hours_per_week')->values(),
        ];
    }

    /** @return Collection<int, object> */
    private function topProgramsByEnrollment(): Collection
    {
        return AcademicBatch::query()
            ->with('program')
            ->withCount('students')
            ->orderByDesc('students_count')
            ->limit(6)
            ->get()
            ->map(fn ($batch) => (object) [
                'name' => $batch->program?->name_ar ?? $batch->name,
                'count' => $batch->students_count,
            ]);
    }
}
