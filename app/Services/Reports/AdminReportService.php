<?php

namespace App\Services\Reports;

use App\Models\AcademicBatch;
use App\Models\AcademicProgram;
use App\Models\AcademicRequest;
use App\Models\AcademicStaff;
use App\Models\AcademicStudent;
use App\Models\AssignmentSubmission;
use App\Models\AttendanceRecord;
use App\Models\CatalogEnrollment;
use App\Models\Certificate;
use App\Models\ExamAttempt;
use App\Models\InstallmentContract;
use App\Models\InstallmentPayment;
use App\Models\InstallmentSchedule;
use App\Models\Order;
use App\Models\RefundRequest;
use App\Models\RegistrationApplication;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\PlatformAnalyticsService;
use App\Support\Reports\ReportFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdminReportService
{
    public function __construct(
        private readonly PlatformAnalyticsService $analytics,
    ) {}

    /**
     * @return list<array{id: string, label: string, description: string}>
     */
    public function areasFor(User $user): array
    {
        $areas = [];

        foreach (config('admin-reports.areas', []) as $id => $meta) {
            $permission = $meta['permission'] ?? 'reports.view';
            if (! $user->canAdmin('reports.view')) {
                continue;
            }
            if ($permission !== 'reports.view' && ! $user->canAdmin($permission)) {
                continue;
            }

            $areas[] = [
                'id' => $id,
                'label' => $meta['label'],
                'description' => $meta['description'] ?? '',
            ];
        }

        return $areas;
    }

    public function canAccessArea(User $user, string $area): bool
    {
        $meta = config("admin-reports.areas.{$area}");
        if (! is_array($meta) || ! $user->canAdmin('reports.view')) {
            return false;
        }

        $permission = $meta['permission'] ?? 'reports.view';

        return $permission === 'reports.view' || $user->canAdmin($permission);
    }

    /**
     * @return array{
     *     kpis: list<array{label: string, value: string|int|float, hint?: string}>,
     *     tables: list<array{title: string, columns: list<string>, rows: list<list<scalar|null>>}>,
     *     export: array{filename: string, headers: list<string>, rows: list<list<scalar|null>>}
     * }
     */
    public function build(string $area, ReportFilter $filter): array
    {
        return match ($area) {
            'students' => $this->students($filter),
            'finance' => $this->finance($filter),
            'installments' => $this->installments($filter),
            'certificates' => $this->certificates($filter),
            'attendance' => $this->attendance($filter),
            'exams' => $this->exams($filter),
            'assignments' => $this->assignments($filter),
            'support' => $this->support($filter),
            'applications' => $this->applications($filter),
            'requests' => $this->requests($filter),
            'catalog' => $this->catalog($filter),
            'staff' => $this->staff($filter),
            'traffic' => $this->traffic($filter),
            default => $this->overview($filter),
        };
    }

    /** @return list<array{id: int, label: string}> */
    public function programOptions(): array
    {
        return AcademicProgram::query()
            ->orderBy('name_ar')
            ->get(['id', 'name_ar', 'code'])
            ->map(fn (AcademicProgram $p) => [
                'id' => $p->id,
                'label' => trim(($p->code ? $p->code.' — ' : '').($p->name_ar ?? '')),
            ])
            ->all();
    }

    /** @return list<array{id: int, label: string, program_id: int|null}> */
    public function batchOptions(?int $programId = null): array
    {
        return AcademicBatch::query()
            ->with('program:id,name_ar,code')
            ->when($programId, fn (Builder $q) => $q->where('program_id', $programId))
            ->orderByDesc('id')
            ->limit(200)
            ->get()
            ->map(fn ($batch) => [
                'id' => $batch->id,
                'program_id' => $batch->program_id,
                'label' => trim(($batch->code ?? $batch->name ?? '#'.$batch->id).' — '.($batch->program?->name_ar ?? '')),
            ])
            ->all();
    }

    private function overview(ReportFilter $filter): array
    {
        $students = $this->scopedStudents($filter)->count();
        $newStudents = $this->scopedStudents($filter)
            ->whereBetween('joined_at', [$filter->from, $filter->to])
            ->count();
        $paidRevenue = (float) $this->paidOrdersInPeriod($filter)->sum('total');
        $orders = $this->scopedOrders($filter)
            ->whereBetween('created_at', [$filter->from, $filter->to])
            ->count();
        $certs = Certificate::query()
            ->whereBetween('issued_at', [$filter->from->toDateString(), $filter->to->toDateString()])
            ->count();
        $tickets = SupportTicket::query()
            ->whereBetween('created_at', [$filter->from, $filter->to])
            ->count();
        $applications = RegistrationApplication::query()
            ->where(fn (Builder $q) => $this->applyCoalescedDateRange($q, 'submitted_at', 'created_at', $filter))
            ->count();
        $attempts = ExamAttempt::query()
            ->where(fn (Builder $q) => $this->applyCoalescedDateRange($q, 'submitted_at', 'created_at', $filter))
            ->count();

        $rows = [
            ['طلاب (نطاق الفلاتر)', $students],
            ['طلاب جدد في الفترة', $newStudents],
            ['طلبات شراء في الفترة', $orders],
            ['إيراد مدفوع (ر.س)', round($paidRevenue, 2)],
            ['شهادات صادرة', $certs],
            ['تذاكر دعم', $tickets],
            ['طلبات انضمام', $applications],
            ['محاولات اختبار', $attempts],
        ];

        return [
            'kpis' => [
                ['label' => 'طلاب جدد', 'value' => $newStudents, 'hint' => 'انضموا خلال الفترة'],
                ['label' => 'إيراد محصّل', 'value' => number_format($paidRevenue, 0).' ر.س', 'hint' => 'طلبات مدفوعة'],
                ['label' => 'طلبات شراء', 'value' => $orders],
                ['label' => 'شهادات', 'value' => $certs],
                ['label' => 'تذاكر دعم', 'value' => $tickets],
                ['label' => 'طلبات انضمام', 'value' => $applications],
            ],
            'tables' => [[
                'title' => 'ملخص الفترة ('.$filter->label().')',
                'columns' => ['المؤشر', 'القيمة'],
                'rows' => $rows,
            ]],
            'export' => [
                'filename' => 'report-overview-'.$filter->from->format('Ymd').'-'.$filter->to->format('Ymd').'.csv',
                'headers' => ['المؤشر', 'القيمة'],
                'rows' => $rows,
            ],
        ];
    }

    private function students(ReportFilter $filter): array
    {
        $base = $this->scopedStudents($filter);
        $inPeriod = (clone $base)->whereBetween('joined_at', [$filter->from, $filter->to]);

        $byStatus = (clone $base)
            ->select('academic_status', DB::raw('COUNT(*) as total'))
            ->groupBy('academic_status')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [$row->academic_status ?: '—', (int) $row->total])
            ->all();

        $byGender = (clone $base)
            ->select('gender', DB::raw('COUNT(*) as total'))
            ->groupBy('gender')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [$row->gender ?: 'غير محدد', (int) $row->total])
            ->all();

        $byProgram = (clone $base)
            ->join('academic_batches', 'academic_students.batch_id', '=', 'academic_batches.id')
            ->join('academic_programs', 'academic_batches.program_id', '=', 'academic_programs.id')
            ->select('academic_programs.name_ar', DB::raw('COUNT(*) as total'))
            ->groupBy('academic_programs.id', 'academic_programs.name_ar')
            ->orderByDesc('total')
            ->limit(15)
            ->get()
            ->map(fn ($row) => [$row->name_ar, (int) $row->total])
            ->all();

        $newByDay = (clone $inPeriod)
            ->whereNotNull('joined_at')
            ->orderBy('joined_at')
            ->get(['joined_at'])
            ->groupBy(fn (AcademicStudent $student) => $student->joined_at?->format('Y-m-d') ?: '—')
            ->map(fn (Collection $group, string $day) => [$day, $group->count()])
            ->values()
            ->all();

        $total = (clone $base)->count();
        $studying = (clone $base)->where('academic_status', 'studying')->count();
        $graduated = (clone $base)->where('academic_status', 'graduated')->count();
        $newCount = (clone $inPeriod)->count();

        return [
            'kpis' => [
                ['label' => 'إجمالي الطلاب', 'value' => $total, 'hint' => 'حسب الفلاتر'],
                ['label' => 'يدرسون', 'value' => $studying],
                ['label' => 'خريجون', 'value' => $graduated],
                ['label' => 'ملتحقون جدد', 'value' => $newCount, 'hint' => 'في الفترة'],
            ],
            'tables' => [
                ['title' => 'حسب الحالة الأكاديمية', 'columns' => ['الحالة', 'العدد'], 'rows' => $byStatus],
                ['title' => 'حسب الجنس', 'columns' => ['الجنس', 'العدد'], 'rows' => $byGender],
                ['title' => 'أعلى البرامج التحافاً', 'columns' => ['البرنامج', 'العدد'], 'rows' => $byProgram],
                ['title' => 'التحاق يومي في الفترة', 'columns' => ['اليوم', 'العدد'], 'rows' => $newByDay],
            ],
            'export' => [
                'filename' => 'report-students-'.$filter->from->format('Ymd').'.csv',
                'headers' => ['البعد', 'القيمة', 'العدد'],
                'rows' => collect($byStatus)->map(fn ($r) => ['الحالة', $r[0], $r[1]])
                    ->merge(collect($byProgram)->map(fn ($r) => ['البرنامج', $r[0], $r[1]]))
                    ->values()
                    ->all(),
            ],
        ];
    }

    private function finance(ReportFilter $filter): array
    {
        $created = $this->scopedOrders($filter)->whereBetween('created_at', [$filter->from, $filter->to]);
        $paid = $this->paidOrdersInPeriod($filter);

        $revenue = (float) (clone $paid)->sum('total');
        $paidCount = (clone $paid)->count();
        $pendingCount = (clone $created)->where('status', 'pending_payment')->count();
        $pendingAmount = (float) (clone $created)->where('status', 'pending_payment')->sum('total');

        $byMethod = (clone $paid)
            ->select('payment_method', DB::raw('COUNT(*) as total'), DB::raw('SUM(total) as amount'))
            ->groupBy('payment_method')
            ->orderByDesc('amount')
            ->get()
            ->map(fn ($row) => [
                $row->payment_method ?: '—',
                (int) $row->total,
                number_format((float) $row->amount, 2),
            ])
            ->all();

        $byStatus = (clone $created)
            ->select('status', DB::raw('COUNT(*) as total'), DB::raw('SUM(total) as amount'))
            ->groupBy('status')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                $row->status,
                (int) $row->total,
                number_format((float) $row->amount, 2),
            ])
            ->all();

        $refunds = RefundRequest::query()
            ->whereBetween('created_at', [$filter->from, $filter->to])
            ->select('status', DB::raw('COUNT(*) as total'), DB::raw('SUM(amount) as amount'))
            ->groupBy('status')
            ->get()
            ->map(fn ($row) => [
                $row->status,
                (int) $row->total,
                number_format((float) $row->amount, 2),
            ])
            ->all();

        $daily = (clone $paid)
            ->get(['paid_at', 'created_at', 'total'])
            ->groupBy(fn (Order $order) => ($order->paid_at ?? $order->created_at)?->format('Y-m-d') ?: '—')
            ->sortKeys()
            ->map(fn (Collection $group, string $day) => [
                $day,
                $group->count(),
                number_format((float) $group->sum('total'), 2),
            ])
            ->values()
            ->all();

        return [
            'kpis' => [
                ['label' => 'إيراد محصّل', 'value' => number_format($revenue, 0).' ر.س'],
                ['label' => 'طلبات مدفوعة', 'value' => $paidCount],
                ['label' => 'بانتظار الدفع', 'value' => $pendingCount, 'hint' => number_format($pendingAmount, 0).' ر.س'],
                ['label' => 'متوسط الطلب', 'value' => $paidCount ? number_format($revenue / $paidCount, 0).' ر.س' : '0'],
            ],
            'tables' => [
                ['title' => 'حسب طريقة الدفع (مدفوع)', 'columns' => ['الطريقة', 'العدد', 'المبلغ'], 'rows' => $byMethod],
                ['title' => 'حسب حالة الطلب', 'columns' => ['الحالة', 'العدد', 'المبلغ'], 'rows' => $byStatus],
                ['title' => 'طلبات الاسترداد', 'columns' => ['الحالة', 'العدد', 'المبلغ'], 'rows' => $refunds],
                ['title' => 'تحصيل يومي', 'columns' => ['اليوم', 'الطلبات', 'المبلغ'], 'rows' => $daily],
            ],
            'export' => [
                'filename' => 'report-finance-'.$filter->from->format('Ymd').'.csv',
                'headers' => ['اليوم', 'الطلبات', 'المبلغ'],
                'rows' => collect($daily)->map(fn ($r) => [$r[0], $r[1], str_replace(',', '', (string) $r[2])])->all(),
            ],
        ];
    }

    private function installments(ReportFilter $filter): array
    {
        $expectedQ = InstallmentSchedule::query()
            ->whereBetween('due_date', [$filter->from->toDateString(), $filter->to->toDateString()])
            ->whereNotIn('status', ['cancelled', 'waived']);

        $expected = (float) (clone $expectedQ)->sum('amount');
        $paidDue = (clone $expectedQ)->where('status', 'paid')->count();
        $pendingDue = (clone $expectedQ)->whereIn('status', ['pending', 'overdue'])->count();

        $collected = (float) InstallmentPayment::query()
            ->where('status', 'success')
            ->whereBetween('paid_at', [$filter->from, $filter->to])
            ->sum('amount');

        $overdue = (float) InstallmentSchedule::query()
            ->where('status', 'overdue')
            ->sum('amount');

        $contracts = InstallmentContract::query()
            ->select('status', DB::raw('COUNT(*) as total'), DB::raw('SUM(total_amount) as amount'))
            ->groupBy('status')
            ->get()
            ->map(fn ($row) => [
                $row->status,
                (int) $row->total,
                number_format((float) $row->amount, 2),
            ])
            ->all();

        $byStatus = (clone $expectedQ)
            ->select('status', DB::raw('COUNT(*) as total'), DB::raw('SUM(amount) as amount'))
            ->groupBy('status')
            ->get()
            ->map(fn ($row) => [
                $row->status,
                (int) $row->total,
                number_format((float) $row->amount, 2),
            ])
            ->all();

        $rate = $expected > 0 ? round(($collected / $expected) * 100, 1) : 0;

        return [
            'kpis' => [
                ['label' => 'مستحق الفترة', 'value' => number_format($expected, 0).' ر.س'],
                ['label' => 'محصّل الفترة', 'value' => number_format($collected, 0).' ر.س'],
                ['label' => 'نسبة التحصيل', 'value' => $rate.'%'],
                ['label' => 'متأخرات حالية', 'value' => number_format($overdue, 0).' ر.س', 'hint' => "مدفوع {$paidDue} / معلّق {$pendingDue}"],
            ],
            'tables' => [
                ['title' => 'أقساط مستحقة في الفترة حسب الحالة', 'columns' => ['الحالة', 'العدد', 'المبلغ'], 'rows' => $byStatus],
                ['title' => 'عقود التقسيط حسب الحالة', 'columns' => ['الحالة', 'العدد', 'إجمالي العقد'], 'rows' => $contracts],
            ],
            'export' => [
                'filename' => 'report-installments-'.$filter->from->format('Ymd').'.csv',
                'headers' => ['الحالة', 'العدد', 'المبلغ'],
                'rows' => collect($byStatus)->map(fn ($r) => [$r[0], $r[1], str_replace(',', '', (string) $r[2])])->all(),
            ],
        ];
    }

    private function certificates(ReportFilter $filter): array
    {
        $base = Certificate::query()
            ->whereBetween('issued_at', [$filter->from->toDateString(), $filter->to->toDateString()]);

        $total = (clone $base)->count();
        $active = (clone $base)->where('status', 'active')->count();
        $revoked = (clone $base)->where('status', 'revoked')->count();

        $byStatus = (clone $base)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get()
            ->map(fn ($row) => [$row->status, (int) $row->total])
            ->all();

        $bySource = (clone $base)
            ->select('source_type', DB::raw('COUNT(*) as total'))
            ->groupBy('source_type')
            ->get()
            ->map(fn ($row) => [$row->source_type ?: '—', (int) $row->total])
            ->all();

        $recent = (clone $base)
            ->orderByDesc('issued_at')
            ->limit(50)
            ->get(['code', 'holder_name', 'program_name', 'status', 'issued_at'])
            ->map(fn (Certificate $c) => [
                $c->code,
                $c->holder_name,
                $c->program_name,
                $c->status,
                optional($c->issued_at)->format('Y-m-d'),
            ])
            ->all();

        return [
            'kpis' => [
                ['label' => 'صادرة في الفترة', 'value' => $total],
                ['label' => 'نشطة', 'value' => $active],
                ['label' => 'ملغاة', 'value' => $revoked],
            ],
            'tables' => [
                ['title' => 'حسب الحالة', 'columns' => ['الحالة', 'العدد'], 'rows' => $byStatus],
                ['title' => 'حسب المصدر', 'columns' => ['المصدر', 'العدد'], 'rows' => $bySource],
                ['title' => 'أحدث الشهادات', 'columns' => ['الرقم', 'الطالب', 'البرنامج', 'الحالة', 'تاريخ الإصدار'], 'rows' => $recent],
            ],
            'export' => [
                'filename' => 'report-certificates-'.$filter->from->format('Ymd').'.csv',
                'headers' => ['الرقم', 'الطالب', 'البرنامج', 'الحالة', 'تاريخ الإصدار'],
                'rows' => $recent,
            ],
        ];
    }

    private function attendance(ReportFilter $filter): array
    {
        $base = AttendanceRecord::query()
            ->whereBetween('created_at', [$filter->from, $filter->to]);

        if ($filter->batchId || $filter->programId) {
            $base->whereHas('student', function (Builder $q) use ($filter): void {
                if ($filter->batchId) {
                    $q->where('batch_id', $filter->batchId);
                }
                if ($filter->programId) {
                    $q->whereHas('batch', fn (Builder $b) => $b->where('program_id', $filter->programId));
                }
            });
        }

        $total = (clone $base)->count();
        $present = (clone $base)->whereIn('status', ['present', 'late'])->count();
        $absent = (clone $base)->where('status', 'absent')->count();
        $rate = $total > 0 ? round(($present / $total) * 100, 1) : 0;

        $byStatus = (clone $base)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [$row->status, (int) $row->total])
            ->all();

        $bySource = (clone $base)
            ->select('source', DB::raw('COUNT(*) as total'))
            ->groupBy('source')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [$row->source ?: '—', (int) $row->total])
            ->all();

        return [
            'kpis' => [
                ['label' => 'سجلات الحضور', 'value' => $total],
                ['label' => 'حاضر / متأخر', 'value' => $present],
                ['label' => 'غائب', 'value' => $absent],
                ['label' => 'نسبة الحضور', 'value' => $rate.'%'],
            ],
            'tables' => [
                ['title' => 'حسب الحالة', 'columns' => ['الحالة', 'العدد'], 'rows' => $byStatus],
                ['title' => 'حسب المصدر', 'columns' => ['المصدر', 'العدد'], 'rows' => $bySource],
            ],
            'export' => [
                'filename' => 'report-attendance-'.$filter->from->format('Ymd').'.csv',
                'headers' => ['الحالة', 'العدد'],
                'rows' => $byStatus,
            ],
        ];
    }

    private function exams(ReportFilter $filter): array
    {
        $base = ExamAttempt::query()
            ->where(fn (Builder $q) => $this->applyCoalescedDateRange($q, 'submitted_at', 'created_at', $filter));

        if ($filter->batchId || $filter->programId) {
            $base->whereHas('student', function (Builder $q) use ($filter): void {
                if ($filter->batchId) {
                    $q->where('batch_id', $filter->batchId);
                }
                if ($filter->programId) {
                    $q->whereHas('batch', fn (Builder $b) => $b->where('program_id', $filter->programId));
                }
            });
        }

        $total = (clone $base)->count();
        $submitted = (clone $base)->whereNotNull('submitted_at')->count();
        $passed = (clone $base)->where('passed', true)->count();
        $failed = (clone $base)->where('passed', false)->whereNotNull('submitted_at')->count();
        $avg = round((float) (clone $base)->whereNotNull('percentage')->avg('percentage'), 1);
        $passRate = $submitted > 0 ? round(($passed / $submitted) * 100, 1) : 0;

        $byStatus = (clone $base)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get()
            ->map(fn ($row) => [$row->status, (int) $row->total])
            ->all();

        $byExam = (clone $base)
            ->join('exams', 'exam_attempts.exam_id', '=', 'exams.id')
            ->select(
                'exams.title',
                DB::raw('COUNT(*) as total'),
                DB::raw('AVG(exam_attempts.percentage) as avg_pct'),
                DB::raw('SUM(CASE WHEN exam_attempts.passed = 1 THEN 1 ELSE 0 END) as passed')
            )
            ->groupBy('exams.id', 'exams.title')
            ->orderByDesc('total')
            ->limit(20)
            ->get()
            ->map(fn ($row) => [
                $row->title ?: '—',
                (int) $row->total,
                round((float) $row->avg_pct, 1),
                (int) $row->passed,
            ])
            ->all();

        return [
            'kpis' => [
                ['label' => 'محاولات', 'value' => $total],
                ['label' => 'مُسلَّمة', 'value' => $submitted],
                ['label' => 'نسبة النجاح', 'value' => $passRate.'%', 'hint' => "ناجح {$passed} / راسب {$failed}"],
                ['label' => 'متوسط النسبة', 'value' => $avg.'%'],
            ],
            'tables' => [
                ['title' => 'حسب حالة المحاولة', 'columns' => ['الحالة', 'العدد'], 'rows' => $byStatus],
                ['title' => 'حسب الاختبار', 'columns' => ['الاختبار', 'المحاولات', 'متوسط %', 'ناجح'], 'rows' => $byExam],
            ],
            'export' => [
                'filename' => 'report-exams-'.$filter->from->format('Ymd').'.csv',
                'headers' => ['الاختبار', 'المحاولات', 'متوسط %', 'ناجح'],
                'rows' => $byExam,
            ],
        ];
    }

    private function assignments(ReportFilter $filter): array
    {
        $base = AssignmentSubmission::query()
            ->where(fn (Builder $q) => $this->applyCoalescedDateRange($q, 'submitted_at', 'created_at', $filter));

        $total = (clone $base)->count();
        $graded = (clone $base)->where('status', 'graded')->count();
        $late = (clone $base)->where('status', 'late')->count();
        $avg = round((float) (clone $base)->whereNotNull('score')->avg('score'), 1);

        $byStatus = (clone $base)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get()
            ->map(fn ($row) => [$row->status, (int) $row->total])
            ->all();

        return [
            'kpis' => [
                ['label' => 'تسليمات', 'value' => $total],
                ['label' => 'مُقيَّمة', 'value' => $graded],
                ['label' => 'متأخرة', 'value' => $late],
                ['label' => 'متوسط الدرجة', 'value' => $avg],
            ],
            'tables' => [
                ['title' => 'حسب الحالة', 'columns' => ['الحالة', 'العدد'], 'rows' => $byStatus],
            ],
            'export' => [
                'filename' => 'report-assignments-'.$filter->from->format('Ymd').'.csv',
                'headers' => ['الحالة', 'العدد'],
                'rows' => $byStatus,
            ],
        ];
    }

    private function support(ReportFilter $filter): array
    {
        $base = SupportTicket::query()->whereBetween('created_at', [$filter->from, $filter->to]);
        if ($filter->status) {
            $base->where('status', $filter->status);
        }

        $total = (clone $base)->count();
        $open = (clone $base)->whereIn('status', ['open', 'pending', 'in_progress'])->count();
        $closed = (clone $base)->whereIn('status', ['closed', 'resolved'])->count();

        $byStatus = (clone $base)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [$row->status, (int) $row->total])
            ->all();

        $byCategory = (clone $base)
            ->select('category', DB::raw('COUNT(*) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [$row->category ?: '—', (int) $row->total])
            ->all();

        return [
            'kpis' => [
                ['label' => 'تذاكر الفترة', 'value' => $total],
                ['label' => 'مفتوحة', 'value' => $open],
                ['label' => 'مغلقة / محلولة', 'value' => $closed],
            ],
            'tables' => [
                ['title' => 'حسب الحالة', 'columns' => ['الحالة', 'العدد'], 'rows' => $byStatus],
                ['title' => 'حسب الفئة', 'columns' => ['الفئة', 'العدد'], 'rows' => $byCategory],
            ],
            'export' => [
                'filename' => 'report-support-'.$filter->from->format('Ymd').'.csv',
                'headers' => ['الحالة', 'العدد'],
                'rows' => $byStatus,
            ],
        ];
    }

    private function applications(ReportFilter $filter): array
    {
        $base = RegistrationApplication::query()
            ->where(fn (Builder $q) => $this->applyCoalescedDateRange($q, 'submitted_at', 'created_at', $filter));
        if ($filter->status) {
            $base->where('status', $filter->status);
        }

        $total = (clone $base)->count();
        $pending = (clone $base)->whereIn('status', ['pending', 'under_review'])->count();
        $approved = (clone $base)->where('status', 'approved')->count();
        $rejected = (clone $base)->where('status', 'rejected')->count();

        $byType = (clone $base)
            ->select('type', DB::raw('COUNT(*) as total'))
            ->groupBy('type')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [$row->type, (int) $row->total])
            ->all();

        $byStatus = (clone $base)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [$row->status, (int) $row->total])
            ->all();

        return [
            'kpis' => [
                ['label' => 'طلبات الفترة', 'value' => $total],
                ['label' => 'قيد المراجعة', 'value' => $pending],
                ['label' => 'مقبولة', 'value' => $approved],
                ['label' => 'مرفوضة', 'value' => $rejected],
            ],
            'tables' => [
                ['title' => 'حسب النوع', 'columns' => ['النوع', 'العدد'], 'rows' => $byType],
                ['title' => 'حسب الحالة', 'columns' => ['الحالة', 'العدد'], 'rows' => $byStatus],
            ],
            'export' => [
                'filename' => 'report-applications-'.$filter->from->format('Ymd').'.csv',
                'headers' => ['النوع', 'العدد'],
                'rows' => $byType,
            ],
        ];
    }

    private function requests(ReportFilter $filter): array
    {
        $base = AcademicRequest::query()
            ->where(fn (Builder $q) => $this->applyCoalescedDateRange($q, 'submitted_at', 'created_at', $filter));
        if ($filter->status) {
            $base->where('status', $filter->status);
        }

        $total = (clone $base)->count();
        $pending = (clone $base)->whereIn('status', ['pending', 'processing'])->count();
        $approved = (clone $base)->where('status', 'approved')->count();
        $rejected = (clone $base)->where('status', 'rejected')->count();

        $byType = (clone $base)
            ->select('type', DB::raw('COUNT(*) as total'))
            ->groupBy('type')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [$row->type, (int) $row->total])
            ->all();

        $byStatus = (clone $base)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [$row->status, (int) $row->total])
            ->all();

        return [
            'kpis' => [
                ['label' => 'طلبات الفترة', 'value' => $total],
                ['label' => 'قيد المعالجة', 'value' => $pending],
                ['label' => 'موافق عليها', 'value' => $approved],
                ['label' => 'مرفوضة', 'value' => $rejected],
            ],
            'tables' => [
                ['title' => 'حسب النوع', 'columns' => ['النوع', 'العدد'], 'rows' => $byType],
                ['title' => 'حسب الحالة', 'columns' => ['الحالة', 'العدد'], 'rows' => $byStatus],
            ],
            'export' => [
                'filename' => 'report-requests-'.$filter->from->format('Ymd').'.csv',
                'headers' => ['النوع', 'العدد'],
                'rows' => $byType,
            ],
        ];
    }

    private function catalog(ReportFilter $filter): array
    {
        $base = CatalogEnrollment::query()->whereBetween('created_at', [$filter->from, $filter->to]);
        if ($filter->status) {
            $base->where('status', $filter->status);
        }

        $total = (clone $base)->count();
        $avgProgress = round((float) (clone $base)->avg('progress_percent'), 1);

        $byStatus = (clone $base)
            ->select('status', DB::raw('COUNT(*) as total'), DB::raw('AVG(progress_percent) as avg_progress'))
            ->groupBy('status')
            ->get()
            ->map(fn ($row) => [
                $row->status,
                (int) $row->total,
                round((float) $row->avg_progress, 1),
            ])
            ->all();

        $byCourse = (clone $base)
            ->join('catalog_courses', 'catalog_enrollments.course_id', '=', 'catalog_courses.id')
            ->select(
                'catalog_courses.title_ar',
                DB::raw('COUNT(*) as total'),
                DB::raw('AVG(catalog_enrollments.progress_percent) as avg_progress')
            )
            ->groupBy('catalog_courses.id', 'catalog_courses.title_ar')
            ->orderByDesc('total')
            ->limit(20)
            ->get()
            ->map(fn ($row) => [
                $row->title_ar ?: '—',
                (int) $row->total,
                round((float) $row->avg_progress, 1),
            ])
            ->all();

        return [
            'kpis' => [
                ['label' => 'اشتراكات الفترة', 'value' => $total],
                ['label' => 'متوسط التقدم', 'value' => $avgProgress.'%'],
            ],
            'tables' => [
                ['title' => 'حسب الحالة', 'columns' => ['الحالة', 'العدد', 'متوسط التقدم %'], 'rows' => $byStatus],
                ['title' => 'أعلى الدورات اشتراكاً', 'columns' => ['الدورة', 'العدد', 'متوسط التقدم %'], 'rows' => $byCourse],
            ],
            'export' => [
                'filename' => 'report-catalog-'.$filter->from->format('Ymd').'.csv',
                'headers' => ['الدورة', 'العدد', 'متوسط التقدم %'],
                'rows' => $byCourse,
            ],
        ];
    }

    private function staff(ReportFilter $filter): array
    {
        $staff = AcademicStaff::query()->with('user')->get();
        $total = $staff->count();
        $active = $staff->where('status', 'active')->count();
        $hours = (int) $staff->sum('hours_per_week');
        $comp = (float) $staff->sum('compensation_total');

        $byStatus = $staff->groupBy('status')
            ->map(fn (Collection $group, $status) => [$status ?: '—', $group->count()])
            ->values()
            ->all();

        $top = $staff->sortByDesc('hours_per_week')
            ->take(20)
            ->values()
            ->map(fn (AcademicStaff $member) => [
                $member->name_ar ?? $member->user?->displayName() ?? '#'.$member->id,
                $member->status,
                (int) $member->hours_per_week,
                number_format((float) $member->compensation_total, 0),
            ])
            ->all();

        return [
            'kpis' => [
                ['label' => 'إجمالي الكوادر', 'value' => $total],
                ['label' => 'نشطون', 'value' => $active],
                ['label' => 'ساعات أسبوعية', 'value' => $hours],
                ['label' => 'إجمالي التعويضات', 'value' => number_format($comp, 0).' ر.س'],
            ],
            'tables' => [
                ['title' => 'حسب الحالة', 'columns' => ['الحالة', 'العدد'], 'rows' => $byStatus],
                ['title' => 'أعلى الساعات', 'columns' => ['الاسم', 'الحالة', 'ساعات/أسبوع', 'التعويض'], 'rows' => $top],
            ],
            'export' => [
                'filename' => 'report-staff-'.now()->format('Ymd').'.csv',
                'headers' => ['الاسم', 'الحالة', 'ساعات/أسبوع', 'التعويض'],
                'rows' => collect($top)->map(fn ($r) => [$r[0], $r[1], $r[2], str_replace(',', '', (string) $r[3])])->all(),
            ],
        ];
    }

    private function traffic(ReportFilter $filter): array
    {
        $span = $filter->daysSpan();
        $days = $span <= 7 ? 7 : ($span <= 30 ? 30 : 90);
        $data = $this->analytics->dashboard($days);
        $kpisData = $data['kpis'] ?? [];

        $kpis = [
            ['label' => 'مشاهدات الصفحات', 'value' => $kpisData['page_views']['value'] ?? 0],
            ['label' => 'الزيارات', 'value' => $kpisData['visits']['value'] ?? 0],
            ['label' => 'زوار فريدون', 'value' => $kpisData['unique_visitors']['value'] ?? 0],
            ['label' => 'تسجيلات دخول', 'value' => $kpisData['logins']['value'] ?? 0],
            ['label' => 'تسجيلات جديدة', 'value' => $kpisData['registrations']['value'] ?? 0],
        ];

        $devices = collect($data['devices'] ?? [])
            ->map(fn (array $row) => [$row['label'] ?? '—', $row['views'] ?? $row['visits'] ?? 0])
            ->all();

        $countries = collect($data['countries'] ?? [])
            ->map(fn (array $row) => [$row['label'] ?? '—', $row['visits'] ?? $row['views'] ?? 0])
            ->all();

        $paths = collect($data['top_pages'] ?? [])
            ->map(fn (array $row) => [$row['label'] ?? '—', $row['views'] ?? $row['visits'] ?? 0])
            ->all();

        return [
            'kpis' => $kpis,
            'tables' => [
                ['title' => 'الأجهزة', 'columns' => ['الجهاز', 'المشاهدات'], 'rows' => $devices],
                ['title' => 'الدول', 'columns' => ['الدولة', 'الزيارات'], 'rows' => $countries],
                ['title' => 'أكثر الصفحات زيارة', 'columns' => ['المسار', 'المشاهدات'], 'rows' => $paths],
            ],
            'export' => [
                'filename' => 'report-traffic-'.$filter->from->format('Ymd').'.csv',
                'headers' => ['المسار', 'المشاهدات'],
                'rows' => $paths,
            ],
        ];
    }

    private function scopedStudents(ReportFilter $filter): Builder
    {
        return AcademicStudent::query()
            ->when($filter->batchId, fn (Builder $q) => $q->where('batch_id', $filter->batchId))
            ->when($filter->programId, function (Builder $q) use ($filter): void {
                $q->whereHas('batch', fn (Builder $b) => $b->where('program_id', $filter->programId));
            })
            ->when($filter->status, fn (Builder $q) => $q->where('academic_status', $filter->status));
    }

    private function scopedOrders(ReportFilter $filter): Builder
    {
        return Order::query()
            ->when($filter->status, fn (Builder $q) => $q->where('status', $filter->status));
    }

    private function paidOrdersInPeriod(ReportFilter $filter): Builder
    {
        return $this->scopedOrders($filter)
            ->where('status', 'paid')
            ->where(fn (Builder $q) => $this->applyCoalescedDateRange($q, 'paid_at', 'created_at', $filter));
    }

    private function applyCoalescedDateRange(Builder $query, string $primary, string $fallback, ReportFilter $filter): void
    {
        $query->where(function (Builder $q) use ($primary, $fallback, $filter): void {
            $q->whereBetween($primary, [$filter->from, $filter->to])
                ->orWhere(function (Builder $inner) use ($primary, $fallback, $filter): void {
                    $inner->whereNull($primary)
                        ->whereBetween($fallback, [$filter->from, $filter->to]);
                });
        });
    }
}
