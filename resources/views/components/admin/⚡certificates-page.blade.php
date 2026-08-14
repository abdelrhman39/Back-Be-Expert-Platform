<?php

use App\Models\AcademicStudent;
use App\Models\CatalogEnrollment;
use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\PlatformSetting;
use App\Services\CertificateService;
use App\Services\ExternalCertificateService;
use App\Support\AcademicStudentOptions;
use App\Support\CatalogEnrollmentOptions;
use App\Support\CertificateAccessPolicy;
use App\Support\CertificateAccessSettings;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new #[Layout('layouts.admin', [
    'adminPageTitle' => 'الشهادات',
    'adminPageDesc' => 'إصدار ومتابعة شهادات التخرج',
    'adminLayout' => 'app',
])]
#[Title('الشهادات | لوحة التحكم')]
class extends Component
{
    use WithFileUploads, WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    public string $status = '';

    public string $programSearch = '';

    public string $source = '';

    public string $issuedFrom = '';

    public string $issuedTo = '';

    public string $sortDir = 'desc';

    public int $perPage = 15;

    public ?int $detailsCertificateId = null;

    // Issue form
    public string $issueSearch = '';

    public ?int $issueStudentId = null;

    public string $issueProgramName = '';

    public ?int $issueTemplateId = null;

    public string $issueStartedAt = '';

    public string $issueEndedAt = '';

    public string $issueIssuedAt = '';

    public string $issueStudentNote = '';

    public bool $showExternalUpload = false;

    public string $externalStudentSearch = '';

    public ?int $externalStudentId = null;

    public string $externalTitle = '';

    public string $externalIssuer = '';

    public string $externalCredentialId = '';

    public string $externalVerificationUrl = '';

    public string $externalRelatedKey = '';

    public string $externalIssuedAt = '';

    public string $externalExpiresAt = '';

    public string $externalStudentNote = '';

    public string $externalNotes = '';

    public string $externalVisibilityMode = 'immediate';

    public string $externalVisibleFrom = '';

    public $externalFile;

    public string $defaultVisibilityMode = 'after_graduation';

    public string $requiredExamType = 'final';

    public bool $portalEnabled = true;

    public bool $autoIssueEnabled = true;

    public bool $autoIssueNotificationsEnabled = true;

    public bool $downloadsEnabled = true;

    public bool $printingEnabled = true;

    public bool $detailsEnabled = true;

    public bool $hideRevoked = false;

    public bool $requireIntegrityForDownload = true;

    public bool $requireActiveForDownload = true;

    public ?int $revokeCertificateId = null;

    public string $revocationReason = '';

    public ?string $savedMessage = null;

    public string $savedMessageKind = 'info'; // info|danger

    private array $issueAllowedStatuses = ['graduated', 'eligible', 'expected'];

    public function mount(): void
    {
        abort_unless(auth()->user()?->canAdmin('certificates.manage'), 403);
        $this->issueTemplateId = CertificateTemplate::query()
            ->where('status', 'active')
            ->orderByDesc('is_default')
            ->value('id');
        $this->issueIssuedAt = now()->toDateString();
        $this->externalIssuedAt = now()->toDateString();
        $this->defaultVisibilityMode = CertificateAccessSettings::defaultVisibilityMode();
        $this->requiredExamType = CertificateAccessSettings::requiredExamType();
        $this->portalEnabled = CertificateAccessSettings::portalEnabled();
        $this->autoIssueEnabled = CertificateAccessSettings::autoIssueEnabled();
        $this->autoIssueNotificationsEnabled = CertificateAccessSettings::autoIssueNotificationsEnabled();
        $this->downloadsEnabled = CertificateAccessSettings::downloadsEnabled();
        $this->printingEnabled = CertificateAccessSettings::printingEnabled();
        $this->detailsEnabled = CertificateAccessSettings::detailsEnabled();
        $this->hideRevoked = CertificateAccessSettings::hideRevoked();
        $this->requireIntegrityForDownload = CertificateAccessSettings::requireIntegrityForDownload();
        $this->requireActiveForDownload = CertificateAccessSettings::requireActiveForDownload();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedProgramSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSource(): void
    {
        $this->resetPage();
    }

    public function updatedIssuedFrom(): void
    {
        $this->resetPage();
    }

    public function updatedIssuedTo(): void
    {
        $this->resetPage();
    }

    public function updatedSortDir(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function openDetails(int $certificateId): void
    {
        $this->detailsCertificateId = $certificateId;
    }

    public function closeDetails(): void
    {
        $this->detailsCertificateId = null;
    }

    public function saveAccessSettings(): void
    {
        abort_unless(auth()->user()?->canAdmin('certificates.manage'), 403);

        $this->validate([
            'defaultVisibilityMode' => ['required', 'in:immediate,after_graduation,after_exam_pass,after_graduation_and_exam'],
            'requiredExamType' => ['required', 'in:any,exam,midterm,final'],
            'portalEnabled' => ['boolean'],
            'autoIssueEnabled' => ['boolean'],
            'autoIssueNotificationsEnabled' => ['boolean'],
            'downloadsEnabled' => ['boolean'],
            'printingEnabled' => ['boolean'],
            'detailsEnabled' => ['boolean'],
            'hideRevoked' => ['boolean'],
            'requireIntegrityForDownload' => ['boolean'],
            'requireActiveForDownload' => ['boolean'],
        ]);

        $settings = [
            'certificate_default_visibility_mode' => [$this->defaultVisibilityMode, 'شرط ظهور الشهادة الموحد', 'string'],
            'certificate_required_exam_type' => [$this->requiredExamType, 'نوع الاختبار المطلوب لاجتياز الشهادة', 'string'],
            'certificate_student_portal_enabled' => [$this->portalEnabled ? '1' : '0', 'إظهار قسم شهاداتي للطالب', 'boolean'],
            'certificate_auto_issue_enabled' => [$this->autoIssueEnabled ? '1' : '0', 'الإصدار التلقائي للشهادات', 'boolean'],
            'certificate_auto_issue_notifications_enabled' => [$this->autoIssueNotificationsEnabled ? '1' : '0', 'إشعار الطالب عند الإصدار التلقائي', 'boolean'],
            'certificate_student_downloads_enabled' => [$this->downloadsEnabled ? '1' : '0', 'السماح بتنزيل الشهادات', 'boolean'],
            'certificate_student_printing_enabled' => [$this->printingEnabled ? '1' : '0', 'السماح بطباعة الشهادات', 'boolean'],
            'certificate_student_details_enabled' => [$this->detailsEnabled ? '1' : '0', 'عرض تفاصيل الشهادات', 'boolean'],
            'certificate_hide_revoked_from_students' => [$this->hideRevoked ? '1' : '0', 'إخفاء الشهادات الملغاة', 'boolean'],
            'certificate_require_integrity_for_download' => [$this->requireIntegrityForDownload ? '1' : '0', 'اشتراط سلامة البصمة للتنزيل', 'boolean'],
            'certificate_require_active_for_download' => [$this->requireActiveForDownload ? '1' : '0', 'اشتراط سريان الشهادة للتنزيل', 'boolean'],
        ];

        foreach ($settings as $key => [$value, $label, $type]) {
            PlatformSetting::set($key, $value, 'certificates', $label, $type, false, null, auth()->id());
        }

        $this->savedMessageKind = 'info';
        $this->savedMessage = 'تم حفظ سياسة ظهور وتنزيل الشهادات.';
    }

    public function chooseIssueStudent(int $studentId): void
    {
        $student = AcademicStudent::query()
            ->with('batch.program')
            ->findOrFail($studentId);

        if (! in_array($student->academic_status, $this->issueAllowedStatuses, true)) {
            $this->savedMessageKind = 'danger';
            $this->savedMessage = 'لا يمكن إصدار شهادة لهذا الطالب (ليس خريجاً/مؤهلاً).';
            return;
        }

        $this->issueStudentId = $student->id;
        $this->issueProgramName = $student->batch?->program?->name_ar ?? '';
        $this->issueStartedAt = $student->batch?->start_date?->toDateString() ?? '';
        $this->issueEndedAt = ($student->batch?->end_date ?? $student->graduated_at)?->toDateString() ?? '';
        $this->savedMessage = null;
        $this->savedMessageKind = 'info';
    }

    // Backward-compat in case the old UI calls openIssue()
    public function openIssue(int $studentId): void
    {
        $this->chooseIssueStudent($studentId);
    }

    public function cancelIssue(): void
    {
        $this->issueStudentId = null;
        $this->issueProgramName = '';
        $this->issueStartedAt = '';
        $this->issueEndedAt = '';
        $this->issueIssuedAt = now()->toDateString();
        $this->issueStudentNote = '';
    }

    public function openExternalUpload(): void
    {
        $this->showExternalUpload = true;
        $this->resetValidation();
    }

    public function closeExternalUpload(): void
    {
        $this->showExternalUpload = false;
        $this->resetExternalUploadForm();
        $this->resetValidation();
    }

    public function chooseExternalStudent(int $studentId): void
    {
        $student = AcademicStudent::query()
            ->whereNotNull('user_id')
            ->findOrFail($studentId);

        $this->externalStudentId = $student->id;
        $this->externalStudentSearch = $student->name_ar;
        $this->externalRelatedKey = '';
    }

    public function uploadExternalCertificate(): void
    {
        abort_unless(auth()->user()?->canAdmin('certificates.manage'), 403);

        $validated = $this->validate([
            'externalStudentId' => ['required', 'integer', 'exists:academic_students,id'],
            'externalTitle' => ['required', 'string', 'max:255'],
            'externalIssuer' => ['required', 'string', 'max:255'],
            'externalCredentialId' => ['nullable', 'string', 'max:255'],
            'externalVerificationUrl' => ['nullable', 'url:http,https', 'max:2000'],
            'externalRelatedKey' => ['required', 'string', 'max:100'],
            'externalIssuedAt' => ['required', 'date'],
            'externalExpiresAt' => ['nullable', 'date', 'after_or_equal:externalIssuedAt'],
            'externalStudentNote' => ['nullable', 'string', 'max:500'],
            'externalNotes' => ['nullable', 'string', 'max:1000'],
            'externalVisibilityMode' => ['required', 'in:immediate,hidden,scheduled'],
            'externalVisibleFrom' => ['nullable', 'required_if:externalVisibilityMode,scheduled', 'date', 'after:now'],
            'externalFile' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ], [], [
            'externalStudentId' => 'الطالب',
            'externalTitle' => 'اسم الشهادة',
            'externalIssuer' => 'جهة الإصدار',
            'externalIssuedAt' => 'تاريخ الإصدار',
            'externalExpiresAt' => 'تاريخ الانتهاء',
            'externalRelatedKey' => 'البرنامج أو الدورة المرتبطة',
            'externalVisibilityMode' => 'إعداد الظهور',
            'externalVisibleFrom' => 'موعد الظهور',
            'externalFile' => 'ملف الشهادة',
        ]);

        $student = AcademicStudent::query()
            ->with(['user', 'batch.program'])
            ->whereNotNull('user_id')
            ->findOrFail($this->externalStudentId);

        $learningContext = collect($this->externalLearningOptions)
            ->firstWhere('key', $this->externalRelatedKey);

        if (! $learningContext) {
            $this->addError('externalRelatedKey', 'البرنامج المحدد لا يرتبط بهذا الطالب.');

            return;
        }

        try {
            app(ExternalCertificateService::class)->upload(
                $student,
                $this->externalFile,
                [
                    'title' => $validated['externalTitle'],
                    'issuer' => $validated['externalIssuer'],
                    'credential_id' => ($validated['externalCredentialId'] ?? '') ?: null,
                    'verification_url' => ($validated['externalVerificationUrl'] ?? '') ?: null,
                    'related_program_type' => $learningContext['type'],
                    'related_program_id' => $learningContext['id'],
                    'related_program_name' => $learningContext['name'],
                    'issued_at' => $validated['externalIssuedAt'],
                    'expires_at' => ($validated['externalExpiresAt'] ?? '') ?: null,
                    'student_note' => ($validated['externalStudentNote'] ?? '') ?: null,
                    'notes' => ($validated['externalNotes'] ?? '') ?: null,
                    'visibility_mode' => $validated['externalVisibilityMode'],
                    'visible_from' => ($validated['externalVisibleFrom'] ?? '') ?: null,
                ],
                auth()->user(),
            );
        } catch (\Throwable $exception) {
            report($exception);
            $this->savedMessageKind = 'danger';
            $this->savedMessage = 'تعذّر رفع الشهادة الخارجية. تحقق من الملف والبيانات ثم أعد المحاولة.';

            return;
        }

        $this->showExternalUpload = false;
        $this->resetExternalUploadForm();
        $this->savedMessageKind = 'info';
        $this->savedMessage = 'تم رفع الشهادة الخارجية وربطها بحساب الطالب بأمان.';
        $this->resetPage();
    }

    private function resetExternalUploadForm(): void
    {
        $this->externalStudentSearch = '';
        $this->externalStudentId = null;
        $this->externalTitle = '';
        $this->externalIssuer = '';
        $this->externalCredentialId = '';
        $this->externalVerificationUrl = '';
        $this->externalRelatedKey = '';
        $this->externalIssuedAt = now()->toDateString();
        $this->externalExpiresAt = '';
        $this->externalStudentNote = '';
        $this->externalNotes = '';
        $this->externalVisibilityMode = 'immediate';
        $this->externalVisibleFrom = '';
        $this->externalFile = null;
    }

    public function issueCertificate(): void
    {
        abort_unless(auth()->user()?->canAdmin('certificates.manage'), 403);

        $this->validate([
            'issueStudentId' => ['required', 'integer', 'min:1'],
            'issueTemplateId' => ['required', 'integer', 'exists:certificate_templates,id'],
            'issueStartedAt' => ['nullable', 'date'],
            'issueEndedAt' => ['nullable', 'date', 'after_or_equal:issueStartedAt'],
            'issueIssuedAt' => ['required', 'date'],
            'issueStudentNote' => ['nullable', 'string', 'max:500'],
        ], [], [
            'issueStudentId' => 'الطالب',
        ]);

        $student = AcademicStudent::query()
            ->with('batch.program')
            ->findOrFail($this->issueStudentId);

        if (! in_array($student->academic_status, $this->issueAllowedStatuses, true)) {
            $this->savedMessageKind = 'danger';
            $this->savedMessage = 'لا يمكن إصدار شهادة لهذا الطالب (ليس خريجاً/مؤهلاً).';
            return;
        }

        try {
            app(CertificateService::class)->issueForStudent(
                $student,
                $this->issueProgramName ?: null,
                auth()->user(),
                CertificateTemplate::query()->findOrFail($this->issueTemplateId),
                [
                    'program_started_at' => $this->issueStartedAt ?: null,
                    'program_ended_at' => $this->issueEndedAt ?: null,
                    'issued_at' => $this->issueIssuedAt,
                    'student_note' => trim($this->issueStudentNote) ?: null,
                ],
            );
        } catch (\Throwable $e) {
            report($e);
            $this->savedMessageKind = 'danger';
            $this->savedMessage = 'تعذّر إصدار ملف الشهادة. تحقق من القالب وصورة الخلفية ثم أعد المحاولة.';

            return;
        }

        $this->issueStudentId = null;
        $this->issueProgramName = '';
        $this->issueStartedAt = '';
        $this->issueEndedAt = '';
        $this->issueIssuedAt = now()->toDateString();
        $this->issueStudentNote = '';

        $this->savedMessageKind = 'info';
        $this->savedMessage = 'تم إصدار الشهادة للطالب.';
    }

    public function openRevoke(int $certificateId): void
    {
        $this->revokeCertificateId = $certificateId;
        $this->revocationReason = '';
        $this->resetErrorBag('revocationReason');
    }

    public function cancelRevoke(): void
    {
        $this->revokeCertificateId = null;
        $this->revocationReason = '';
    }

    public function confirmRevoke(): void
    {
        abort_unless(auth()->user()?->canAdmin('certificates.manage'), 403);
        $this->validate([
            'revokeCertificateId' => ['required', 'integer', 'exists:certificates,id'],
            'revocationReason' => ['required', 'string', 'min:5', 'max:1000'],
        ], [], [
            'revocationReason' => 'سبب الإلغاء',
        ]);

        $cert = Certificate::query()->findOrFail($this->revokeCertificateId);
        app(CertificateService::class)->revoke($cert, trim($this->revocationReason));
        $this->cancelRevoke();

        $this->savedMessageKind = 'info';
        $this->savedMessage = 'تم إلغاء الشهادة.';
    }

    #[Computed]
    public function certificateTemplates()
    {
        return CertificateTemplate::query()
            ->where('status', 'active')
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function externalStudentCandidates()
    {
        $term = trim($this->externalStudentSearch);

        return AcademicStudent::query()
            ->with('batch.program')
            ->whereNotNull('user_id')
            ->when($term !== '', function ($query) use ($term): void {
                $query->where(function ($inner) use ($term): void {
                    $inner->where('name_ar', 'like', "%{$term}%")
                        ->orWhere('name_en', 'like', "%{$term}%")
                        ->orWhere('academic_id', 'like', "%{$term}%")
                        ->orWhere('national_id', 'like', "%{$term}%");
                });
            })
            ->latest('id')
            ->limit(8)
            ->get();
    }

    #[Computed]
    public function externalLearningOptions(): array
    {
        if (! $this->externalStudentId) {
            return [];
        }

        $student = AcademicStudent::query()
            ->with('batch.program')
            ->whereNotNull('user_id')
            ->find($this->externalStudentId);

        if (! $student) {
            return [];
        }

        $options = [];
        $program = $student->batch?->program;

        if ($program) {
            $options[] = [
                'key' => 'academic_program:'.$program->id,
                'type' => 'academic_program',
                'id' => $program->id,
                'name' => $program->name_ar,
                'kind' => match ($program->type) {
                    'diploma' => 'دبلوم أكاديمي',
                    'program' => 'برنامج أكاديمي',
                    default => 'برنامج أكاديمي',
                },
                'status' => AcademicStudentOptions::academicStatusLabel($student->academic_status),
            ];
        }

        $enrollments = CatalogEnrollment::query()
            ->with(['course', 'orderItem'])
            ->where('user_id', $student->user_id)
            ->latest('enrolled_at')
            ->get();

        foreach ($enrollments as $enrollment) {
            $options[] = [
                'key' => 'catalog_enrollment:'.$enrollment->id,
                'type' => 'catalog_enrollment',
                'id' => $enrollment->id,
                'name' => $enrollment->displayTitle(),
                'kind' => 'دورة تدريبية',
                'status' => CatalogEnrollmentOptions::statusLabel($enrollment->status),
            ];
        }

        return $options;
    }

    #[Computed]
    public function certs()
    {
        return Certificate::query()
            ->with(['template:id,name', 'issuer:id,name', 'academicStudent:id,academic_status,section_id'])
            ->when(filled($this->search), function ($q) {
                $term = trim($this->search);

                $q->where(function ($q) use ($term) {
                    $q->where('holder_name', 'like', "%{$term}%")
                        ->orWhere('code', 'like', "%{$term}%")
                        ->orWhere('external_issuer', 'like', "%{$term}%")
                        ->orWhere('external_credential_id', 'like', "%{$term}%");
                });
            })
            ->when(filled($this->status), fn ($q) => $q->where('status', $this->status))
            ->when(filled($this->source), fn ($q) => $q->where('source_type', $this->source))
            ->when(filled($this->programSearch), function ($q) {
                $term = trim($this->programSearch);
                $q->where('program_name', 'like', "%{$term}%");
            })
            ->when(filled($this->issuedFrom), fn ($q) => $q->whereDate('issued_at', '>=', $this->issuedFrom))
            ->when(filled($this->issuedTo), fn ($q) => $q->whereDate('issued_at', '<=', $this->issuedTo))
            ->orderBy('issued_at', $this->sortDir === 'asc' ? 'asc' : 'desc')
            ->orderByDesc('id')
            ->paginate(max(5, min($this->perPage, 50)));
    }

    #[Computed]
    public function certStats(): array
    {
        $base = Certificate::query();

        return [
            'total' => (clone $base)->count(),
            'active' => (clone $base)->where('status', 'active')->count(),
            'revoked' => (clone $base)->where('status', 'revoked')->count(),
            'month' => (clone $base)->whereBetween('issued_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
        ];
    }

    #[Computed]
    public function detailsCertificate(): ?Certificate
    {
        if (! $this->detailsCertificateId) {
            return null;
        }

        return Certificate::query()
            ->with(['template:id,name', 'issuer:id,name', 'academicStudent:id,academic_id,national_id,academic_status,section_id'])
            ->find($this->detailsCertificateId);
    }

    #[Computed]
    public function issueStudent(): ?AcademicStudent
    {
        if (! $this->issueStudentId) {
            return null;
        }

        return AcademicStudent::query()
            ->with('batch.program')
            ->find($this->issueStudentId);
    }

    #[Computed]
    public function issueCandidates()
    {
        $q = AcademicStudent::query()
            ->with('batch.program')
            ->whereIn('academic_status', $this->issueAllowedStatuses)
            ->orderByDesc('joined_at');

        $term = trim($this->issueSearch);
        if ($term !== '') {
            $q->where(function ($q) use ($term) {
                $q->where('name_ar', 'like', "%{$term}%")
                    ->orWhere('academic_id', 'like', "%{$term}%")
                    ->orWhere('national_id', 'like', "%{$term}%");
            });
        }

        return $q->limit(20)->get();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->status = '';
        $this->programSearch = '';
        $this->source = '';
        $this->issuedFrom = '';
        $this->issuedTo = '';
        $this->sortDir = 'desc';
        $this->resetPage();
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.certificates'),
])

@if ($savedMessage)
    <div class="admin-alert admin-alert--{{ $savedMessageKind === 'danger' ? 'danger' : 'info' }} is-visible">{{ $savedMessage }}</div>
@endif

<section class="cert-admin-hero">
    <div class="cert-admin-hero__content">
        <span class="cert-admin-hero__eyebrow"><i class="fa-solid fa-shield-halved"></i> مركز إدارة الاعتمادات</span>
        <h1>إصدار الشهادات ومتابعتها</h1>
        <p>أنشئ شهادات موثّقة، نزّل ملفات PDF، وتابع حالة كل شهادة من مساحة عمل موحّدة.</p>
    </div>
    <div class="cert-admin-hero__actions">
        <button type="button" class="cert-admin-hero__issue" onclick="document.getElementById('issue-certificate')?.scrollIntoView({behavior:'smooth'})">
            <i class="fa-solid fa-award"></i> إصدار شهادة
        </button>
        <button type="button" wire:click="openExternalUpload">
            <i class="fa-solid fa-cloud-arrow-up"></i> رفع شهادة خارجية
        </button>
        @canAdmin('certificate-templates.manage')
            <a href="{{ route('admin.certificate-templates') }}"><i class="fa-solid fa-pen-ruler"></i> قوالب الشهادات</a>
        @endcanAdmin
    </div>
</section>

<section class="cert-admin-stats">
    <article>
        <div class="cert-admin-stats__icon is-total"><i class="fa-solid fa-certificate"></i></div>
        <div><span>إجمالي الشهادات</span><strong>{{ number_format($this->certStats['total']) }}</strong><small>جميع الإصدارات المسجلة</small></div>
    </article>
    <article>
        <div class="cert-admin-stats__icon is-active"><i class="fa-solid fa-circle-check"></i></div>
        <div><span>الشهادات السارية</span><strong>{{ number_format($this->certStats['active']) }}</strong><small>صالحة وقابلة للتحقق</small></div>
    </article>
    <article>
        <div class="cert-admin-stats__icon is-revoked"><i class="fa-solid fa-ban"></i></div>
        <div><span>الشهادات الملغاة</span><strong>{{ number_format($this->certStats['revoked']) }}</strong><small>تم إيقاف صلاحيتها</small></div>
    </article>
    <article>
        <div class="cert-admin-stats__icon is-month"><i class="fa-solid fa-calendar-plus"></i></div>
        <div><span>صادرة هذا الشهر</span><strong>{{ number_format($this->certStats['month']) }}</strong><small>{{ now()->translatedFormat('F Y') }}</small></div>
    </article>
</section>

<details class="cert-policy-panel">
    <summary>
        <span class="cert-policy-panel__icon"><i class="fa-solid fa-sliders"></i></span>
        <span><strong>سياسة ظهور الشهادات للطلاب</strong><small>تحكم مركزي في النشر والتنزيل والطباعة وشروط الحماية</small></span>
        <span class="cert-policy-panel__summary-state"><i class="fa-solid fa-chevron-down"></i></span>
    </summary>
    <form wire:submit="saveAccessSettings">
        <div class="cert-policy-intro">
            <i class="fa-solid fa-circle-info"></i>
            <div><strong>قاعدة واحدة لكل الطلاب</strong><p>يُحفظ الشرط مرة واحدة ويُطبق فوراً على جميع الشهادات الحالية والجديدة. لا تحتاج إلى فتح كل طالب أو كل شهادة لتحديد الظهور.</p></div>
        </div>

        <div class="cert-auto-flow">
            <article><span>1</span><i class="fa-solid fa-user-check"></i><div><strong>تحقق الشرط</strong><small>تخرج أو نجاح بالاختبار</small></div></article>
            <i class="fa-solid fa-arrow-left"></i>
            <article><span>2</span><i class="fa-solid fa-gears"></i><div><strong>إصدار آلي</strong><small>باستخدام القالب الافتراضي</small></div></article>
            <i class="fa-solid fa-arrow-left"></i>
            <article><span>3</span><i class="fa-solid fa-bell"></i><div><strong>إشعار الطالب</strong><small>داخل المنصة والبريد</small></div></article>
            <i class="fa-solid fa-arrow-left"></i>
            <article><span>4</span><i class="fa-solid fa-shield-halved"></i><div><strong>عرض وتنزيل</strong><small>PDF موثق ورمز تحقق</small></div></article>
        </div>

        <div class="cert-policy-grid">
            <label class="cert-policy-select">
                <span><i class="fa-solid fa-graduation-cap"></i> شرط ظهور الشهادة الموحد</span>
                <select wire:model.live="defaultVisibilityMode">
                    <option value="immediate">فور الإصدار</option>
                    <option value="after_graduation">بعد اعتماد التخرج</option>
                    <option value="after_exam_pass">بعد اجتياز الاختبار</option>
                    <option value="after_graduation_and_exam">بعد التخرج واجتياز الاختبار معاً</option>
                </select>
                <small>يُعاد فحص هذا الشرط تلقائياً عند كل دخول للطالب.</small>
            </label>

            @if (in_array($defaultVisibilityMode, ['after_exam_pass', 'after_graduation_and_exam'], true))
                <label class="cert-policy-select">
                    <span><i class="fa-solid fa-file-circle-check"></i> نوع الاختبار المطلوب</span>
                    <select wire:model="requiredExamType">
                        <option value="final">اختبار نهائي ناجح</option>
                        <option value="exam">اختبار عادي ناجح</option>
                        <option value="midterm">اختبار نصفي ناجح</option>
                        <option value="any">أي اختبار مرصود ناجح</option>
                    </select>
                    <small>يجب أن تكون المحاولة مصححة وناجحة، ومن اختبارات شعبة الطالب الحالية.</small>
                </label>
            @endif

            <label class="cert-policy-toggle">
                <input type="checkbox" wire:model="portalEnabled">
                <span class="cert-policy-toggle__switch"></span>
                <span><strong>إظهار قسم شهاداتي</strong><small>إخفاؤه يزيل البند من قائمة الطالب ويمنع الوصول المباشر.</small></span>
            </label>
            <label class="cert-policy-toggle cert-policy-toggle--featured">
                <input type="checkbox" wire:model.live="autoIssueEnabled">
                <span class="cert-policy-toggle__switch"></span>
                <span><strong>الإصدار التلقائي</strong><small>ينشئ الشهادة بالقالب الافتراضي فور تحقق الشرط دون تدخل الإدارة.</small></span>
            </label>
            <label class="cert-policy-toggle">
                <input type="checkbox" wire:model="autoIssueNotificationsEnabled" @disabled(! $autoIssueEnabled)>
                <span class="cert-policy-toggle__switch"></span>
                <span><strong>إشعار الطالب عند الإصدار</strong><small>إشعار داخل المنصة والبريد حسب تفضيلات الطالب.</small></span>
            </label>
            <label class="cert-policy-toggle">
                <input type="checkbox" wire:model="downloadsEnabled">
                <span class="cert-policy-toggle__switch"></span>
                <span><strong>السماح بتنزيل PDF</strong><small>المفتاح العام لتنزيل الشهادات من بوابة الطالب.</small></span>
            </label>
            <label class="cert-policy-toggle">
                <input type="checkbox" wire:model="printingEnabled">
                <span class="cert-policy-toggle__switch"></span>
                <span><strong>السماح بالطباعة</strong><small>إظهار زر الطباعة داخل صفحة الشهادة.</small></span>
            </label>
            <label class="cert-policy-toggle">
                <input type="checkbox" wire:model="detailsEnabled">
                <span class="cert-policy-toggle__switch"></span>
                <span><strong>عرض التفاصيل الموسعة</strong><small>الفترة والرمز وحالة التحقق داخل بطاقة الطالب.</small></span>
            </label>
            <label class="cert-policy-toggle">
                <input type="checkbox" wire:model="hideRevoked">
                <span class="cert-policy-toggle__switch"></span>
                <span><strong>إخفاء الشهادات الملغاة</strong><small>بدلاً من عرضها للطالب بحالة «ملغاة».</small></span>
            </label>
            <label class="cert-policy-toggle">
                <input type="checkbox" wire:model="requireActiveForDownload">
                <span class="cert-policy-toggle__switch"></span>
                <span><strong>اشتراط سريان الشهادة</strong><small>يمنع تنزيل الشهادات الملغاة أو المنتهية.</small></span>
            </label>
            <label class="cert-policy-toggle">
                <input type="checkbox" wire:model="requireIntegrityForDownload">
                <span class="cert-policy-toggle__switch"></span>
                <span><strong>اشتراط سلامة البصمة</strong><small>يمنع تنزيل أي شهادة تغيرت بياناتها بعد الإصدار.</small></span>
            </label>
        </div>
        <footer class="cert-policy-panel__footer">
            <span><i class="fa-solid fa-shield-halved"></i> الظهور والتنزيل يُفحصان لحظياً لكل الطلاب وفق هذه القاعدة.</span>
            <button type="submit"><i class="fa-solid fa-floppy-disk"></i> حفظ وتطبيق على الجميع</button>
        </footer>
    </form>
</details>

<section class="admin-crud-card cert-management-panel">
    <div class="admin-crud-card__head admin-crud-card__head--row">
        <div>
            <span class="cert-section-kicker">سجل الشهادات</span>
            <h2>الشهادات الصادرة</h2>
            <p class="admin-crud-card__meta">ابحث وراجع ونزّل ملفات الشهادات الموثقة.</p>
        </div>
        @canAdmin('certificate-templates.manage')
            <a href="{{ route('admin.certificate-templates') }}" class="admin-btn-primary admin-btn-primary--sm">
                <i class="fa-solid fa-pen-ruler"></i> منشئ قوالب الشهادات
            </a>
        @endcanAdmin
    </div>

    <div class="cert-filter-panel">
        <div class="cert-filter-panel__main">
            <label class="cert-filter-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="ابحث باسم حامل الشهادة أو رمزها...">
                <kbd>بحث فوري</kbd>
            </label>
            <div class="cert-status-chips">
                <button type="button" class="cert-status-chip {{ $status === '' ? 'is-active' : '' }}" wire:click="$set('status','')" title="كل الشهادات">
                    <i class="fa-solid fa-layer-group"></i> الكل <span class="cert-status-chip__count">{{ $this->certStats['total'] }}</span>
                </button>
                <button type="button" class="cert-status-chip cert-status-chip--active {{ $status === 'active' ? 'is-active' : '' }}" wire:click="$set('status','active')" title="الشهادات السارية">
                    <i class="fa-solid fa-circle-check"></i> سارية <span class="cert-status-chip__count">{{ $this->certStats['active'] }}</span>
                </button>
                <button type="button" class="cert-status-chip cert-status-chip--revoked {{ $status === 'revoked' ? 'is-active' : '' }}" wire:click="$set('status','revoked')" title="الشهادات الملغاة">
                    <i class="fa-solid fa-ban"></i> ملغاة <span class="cert-status-chip__count">{{ $this->certStats['revoked'] }}</span>
                </button>
            </div>
        </div>

        <div class="cert-filter-panel__row">
            <label class="cert-filter-field">
                <span><i class="fa-solid fa-graduation-cap"></i> البرنامج</span>
                <input type="search" wire:model.live.debounce.300ms="programSearch" placeholder="اسم البرنامج (جزئياً)...">
            </label>

            <div class="cert-filter-field cert-filter-field--range">
                <span><i class="fa-regular fa-calendar-days"></i> فترة الإصدار</span>
                <div class="cert-filter-range">
                    <input type="date" wire:model.live="issuedFrom" title="من تاريخ الإصدار">
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                    <input type="date" wire:model.live="issuedTo" title="إلى تاريخ الإصدار">
                </div>
            </div>

            <label class="cert-filter-field cert-filter-field--sort">
                <span><i class="fa-solid fa-certificate"></i> نوع الشهادة</span>
                <select wire:model.live="source">
                    <option value="">كل الأنواع</option>
                    <option value="platform">صادرة من المنصة</option>
                    <option value="external">شهادات خارجية</option>
                </select>
            </label>

            <label class="cert-filter-field cert-filter-field--sort">
                <span><i class="fa-solid fa-arrow-down-wide-short"></i> الترتيب</span>
                <select wire:model.live="sortDir">
                    <option value="desc">الأحدث أولاً</option>
                    <option value="asc">الأقدم أولاً</option>
                </select>
            </label>

            <button type="button" class="cert-clear-filters" wire:click="clearFilters" title="مسح كل الفلاتر">
                <i class="fa-solid fa-rotate-left"></i> إعادة ضبط
            </button>
        </div>
    </div>

    @php($certs = $this->certs)
    <div class="cert-results-toolbar">
        <div class="cert-results-toolbar__summary">
            <i class="fa-solid fa-list-check"></i>
            <span>عرض <strong>{{ $certs->count() }}</strong> من أصل <strong>{{ number_format($certs->total()) }}</strong> شهادة</span>
            @if (filled($search) || filled($status) || filled($source) || filled($programSearch) || filled($issuedFrom) || filled($issuedTo))
                <em class="cert-results-toolbar__filtered"><i class="fa-solid fa-filter"></i> نتائج مفلترة</em>
            @endif
        </div>
        <label class="cert-results-toolbar__perpage">
            عدد الصفوف
            <select wire:model.live="perPage">
                <option value="10">10</option>
                <option value="15">15</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
        </label>
    </div>

    <div class="admin-table-wrap cert-table-wrap" wire:loading.class="is-loading" wire:target="search,status,source,programSearch,issuedFrom,issuedTo,sortDir,perPage,gotoPage,nextPage,previousPage">
        <table class="admin-table cert-table">
            <colgroup>
                <col class="cert-col-code">
                <col class="cert-col-holder">
                <col class="cert-col-program">
                <col class="cert-col-issued">
                <col class="cert-col-status">
                <col class="cert-col-actions">
            </colgroup>
            <thead>
                <tr>
                    <th>الشهادة</th>
                    <th>حامل الشهادة</th>
                    <th>البرنامج</th>
                    <th>الإصدار</th>
                    <th>الحالة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($certs as $cert)
                    <tr wire:key="c-{{ $cert->id }}">
                        <td>
                            <div class="cert-identity">
                                <code class="cert-code" dir="ltr">{{ $cert->code }}</code>
                                @if ($cert->isExternal())
                                    <small class="cert-source-external"><i class="fa-solid fa-building-columns"></i> شهادة خارجية — {{ $cert->external_issuer }}</small>
                                @else
                                    <small><i class="fa-solid fa-layer-group"></i> {{ $cert->template?->name ?? 'قالب النظام' }}{{ $cert->template_version ? ' — v'.$cert->template_version : '' }}</small>
                                @endif
                            </div>
                        </td>
                        <td><div class="cert-holder"><span>{{ mb_substr($cert->holder_name, 0, 1) }}</span><strong>{{ $cert->holder_name }}</strong></div></td>
                        <td>
                            <div class="cert-program-cell">
                                <span class="cert-program">{{ $cert->program_name }}</span>
                                @if ($cert->program_started_at || $cert->program_ended_at)
                                    <small dir="ltr">{{ $cert->program_started_at?->format('Y-m-d') ?? '—' }} → {{ $cert->program_ended_at?->format('Y-m-d') ?? '—' }}</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="cert-issue-cell">
                                <time class="cert-date" datetime="{{ $cert->issued_at->toDateString() }}"><i class="fa-regular fa-calendar"></i>{{ $cert->issued_at->format('Y-m-d') }}</time>
                                @if ($cert->issuer)
                                    <small><i class="fa-regular fa-user"></i> {{ $cert->issuer->name }}</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if ($cert->status === 'revoked')
                                <span class="cert-status-badge is-revoked" @if($cert->revocation_reason) title="{{ $cert->revocation_reason }}" @endif><i class="fa-solid fa-ban"></i> ملغاة</span>
                            @elseif ($cert->expires_at && $cert->expires_at->isPast())
                                <span class="cert-status-badge is-expired"><i class="fa-regular fa-clock"></i> منتهية</span>
                            @elseif (! $cert->hasValidIntegrity())
                                <span class="cert-status-badge is-tampered" title="بصمة البيانات لا تطابق النسخة الأصلية"><i class="fa-solid fa-triangle-exclamation"></i> بيانات معدّلة</span>
                            @else
                                <span class="cert-status-badge is-valid"><i class="fa-solid fa-circle-check"></i> سارية</span>
                            @endif
                            <small class="cert-student-visibility {{ CertificateAccessPolicy::isVisible($cert) ? 'is-visible' : 'is-hidden' }}">
                                <i class="fa-solid {{ CertificateAccessPolicy::isVisible($cert) ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                {{ CertificateAccessPolicy::isVisible($cert) ? 'ظاهرة للطالب' : 'غير ظاهرة' }}
                            </small>
                        </td>
                        <td>
                            <div class="cert-row-actions">
                                <button type="button" class="cert-action cert-action--details" wire:click="openDetails({{ $cert->id }})" title="عرض التفاصيل الكاملة"><i class="fa-regular fa-eye"></i><span>تفاصيل</span></button>
                                <a href="{{ route('admin.certificates.download', $cert) }}" class="cert-action cert-action--primary" title="تنزيل PDF">
                                    <i class="fa-solid fa-file-arrow-down"></i><span>{{ $cert->isExternal() ? 'الملف' : 'PDF' }}</span>
                                </a>
                                <span class="cert-action-group">
                                    <a href="{{ $cert->verifyUrl() }}" target="_blank" rel="noopener" class="cert-action cert-action--icon" title="فتح صفحة التحقق" aria-label="فتح صفحة التحقق"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                                    <button
                                        type="button"
                                        class="cert-action cert-action--icon js-copy-url"
                                        data-copy-url="{{ $cert->verifyUrl() }}"
                                        title="نسخ رابط التحقق"
                                        aria-label="نسخ رابط التحقق"
                                    >
                                        <i class="fa-regular fa-copy"></i>
                                    </button>
                                    @if ($cert->isValid())
                                        <button type="button" class="cert-action cert-action--icon cert-action--danger" wire:click="openRevoke({{ $cert->id }})" title="إلغاء صلاحية الشهادة" aria-label="إلغاء صلاحية الشهادة"><i class="fa-solid fa-ban"></i></button>
                                    @endif
                                </span>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><div class="cert-empty-state"><i class="fa-regular fa-folder-open"></i><strong>لا توجد شهادات مطابقة</strong><span>غيّر معايير البحث أو ابدأ بإصدار شهادة جديدة.</span><button type="button" class="cert-clear-filters" style="width:auto;margin-top:.4rem" wire:click="clearFilters"><i class="fa-solid fa-rotate-left"></i> إعادة ضبط الفلاتر</button></div></td></tr>
                @endforelse
            </tbody>
        </table>

        @if ($certs->hasPages())
            <div class="admin-pagination-wrap">
                {{ $certs->links() }}
            </div>
        @endif
    </div>
</section>

<section class="admin-crud-card cert-issue-studio" id="issue-certificate">
    <div class="admin-crud-card__head admin-crud-card__head--row">
        <div>
            <span class="cert-section-kicker">إصدار جديد</span>
            <h2>إصدار شهادة لطالب</h2>
            <p class="admin-crud-card__meta">اختر الطالب والقالب، راجع البيانات، ثم أنشئ ملف الشهادة الموثق.</p>
        </div>
        <ol class="cert-issue-stepper" aria-label="خطوات إصدار الشهادة">
            <li class="is-active"><span>1</span> اختيار الطالب</li>
            <li class="{{ $issueStudentId ? 'is-active' : '' }}"><span>2</span> بيانات الشهادة</li>
            <li class="{{ $issueStudentId && $issueTemplateId ? 'is-active' : '' }}"><span>3</span> الإصدار</li>
        </ol>
    </div>

    <div class="cert-issue-layout">
        <div class="cert-issue-panel">
            <header class="cert-issue-panel__head">
                <span class="cert-issue-panel__step">1</span>
                <div>
                    <strong>اختر الطالب</strong>
                    <small>ابحث بين الخريجين والمؤهلين ثم اختر من القائمة.</small>
                </div>
            </header>

            <label class="cert-issue-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="search" wire:model.live.debounce.300ms="issueSearch" placeholder="الاسم / الرقم الأكاديمي / الهوية...">
            </label>

            <div class="cert-issue-candidates">
                @if ($this->issueCandidates->isEmpty())
                    <div class="cert-issue-candidates__empty">
                        <i class="fa-regular fa-user"></i>
                        <span>لا يوجد نتائج مطابقة</span>
                    </div>
                @else
                    @foreach ($this->issueCandidates as $student)
                        <button
                            type="button"
                            class="cert-candidate-row {{ $issueStudentId === $student->id ? 'is-active' : '' }}"
                            wire:click="chooseIssueStudent({{ $student->id }})"
                        >
                            <span class="cert-candidate-row__avatar">{{ mb_substr($student->name_ar, 0, 1) }}</span>
                            <span class="cert-candidate-row__info">
                                <strong>{{ $student->name_ar }}</strong>
                                <small>#{{ $student->academic_id ?? '—' }}</small>
                            </span>
                            <span class="cert-issue-candidate__meta">
                                <span class="cert-issue-candidate__program">{{ $student->batch?->program?->name_ar ?? '' }}</span>
                                <span class="cert-issue-candidate__status">{{ AcademicStudentOptions::academicStatusLabel($student->academic_status) }}</span>
                            </span>
                            <i class="fa-solid fa-circle-check cert-candidate-row__check"></i>
                        </button>
                    @endforeach
                @endif
            </div>

            @if ($issueStudentId)
                <div class="cert-issue-selected">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>الطالب محدد <code dir="ltr">#{{ $issueStudentId }}</code></span>
                    <button type="button" wire:click="cancelIssue"><i class="fa-solid fa-xmark"></i> إلغاء التحديد</button>
                </div>
            @endif
        </div>

        <div class="cert-issue-panel {{ $issueStudentId ? '' : 'is-waiting' }}">
            <header class="cert-issue-panel__head">
                <span class="cert-issue-panel__step">2</span>
                <div>
                    <strong>بيانات الشهادة</strong>
                    <small>راجع بيانات الطالب واضبط القالب والتواريخ قبل الإصدار.</small>
                </div>
            </header>

            @php($sel = $this->issueStudent)
            @if ($sel)
                <div class="cert-issue-summary">
                    <span class="cert-issue-summary__avatar">{{ mb_substr($sel->name_ar, 0, 1) }}</span>
                    <div class="cert-issue-summary__info">
                        <strong>{{ $sel->name_ar }}</strong>
                        <small>{{ $sel->batch?->program?->name_ar ?? '—' }}</small>
                    </div>
                    <div class="cert-issue-summary__tags">
                        @if ($sel->academic_id)
                            <span dir="ltr">#{{ $sel->academic_id }}</span>
                        @endif
                        <span class="is-status">{{ AcademicStudentOptions::academicStatusLabel($sel->academic_status) }}</span>
                    </div>
                </div>
            @else
                <div class="cert-issue-summary cert-issue-summary--empty">
                    <i class="fa-regular fa-hand-pointer"></i>
                    <span>اختر طالباً من الخطوة الأولى لعرض بياناته هنا.</span>
                </div>
            @endif

            <div class="cert-issue-fields">
                <label class="cert-issue-field cert-issue-field--full">
                    <span><i class="fa-solid fa-graduation-cap"></i> اسم البرنامج في الشهادة</span>
                    <input type="text" class="admin-control" wire:model="issueProgramName" placeholder="يُملأ تلقائياً من برنامج الدفعة" @disabled(! $issueStudentId)>
                </label>

                <label class="cert-issue-field cert-issue-field--full">
                    <span><i class="fa-solid fa-layer-group"></i> قالب الشهادة</span>
                    <select class="admin-control" wire:model="issueTemplateId" @disabled(! $issueStudentId)>
                        <option value="">اختر قالباً فعالاً</option>
                        @foreach ($this->certificateTemplates as $certificateTemplate)
                            <option value="{{ $certificateTemplate->id }}">
                                {{ $certificateTemplate->name }}{{ $certificateTemplate->is_default ? ' — الافتراضي' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('issueTemplateId')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                </label>

                <label class="cert-issue-field">
                    <span><i class="fa-regular fa-calendar"></i> تاريخ بدء البرنامج</span>
                    <input type="date" class="admin-control" wire:model="issueStartedAt" @disabled(! $issueStudentId)>
                    @error('issueStartedAt')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                </label>
                <label class="cert-issue-field">
                    <span><i class="fa-regular fa-calendar-check"></i> تاريخ انتهاء البرنامج</span>
                    <input type="date" class="admin-control" wire:model="issueEndedAt" @disabled(! $issueStudentId)>
                    @error('issueEndedAt')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                </label>
                <label class="cert-issue-field">
                    <span><i class="fa-solid fa-stamp"></i> تاريخ إصدار الشهادة</span>
                    <input type="date" class="admin-control" wire:model="issueIssuedAt" @disabled(! $issueStudentId)>
                    @error('issueIssuedAt')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                </label>
            </div>

            <div class="cert-issue-access">
                <div class="cert-issue-access__head">
                    <i class="fa-solid fa-users-gear"></i>
                    <div>
                        <strong>الظهور محكوم بالسياسة العامة</strong>
                        <small>لا يوجد إعداد خاص لهذا الطالب. ستظهر الشهادة تلقائياً عند تحقق الشرط الموحد المحفوظ أعلى الصفحة.</small>
                    </div>
                </div>
                <label class="cert-issue-field cert-issue-field--full">
                    <span><i class="fa-regular fa-message"></i> رسالة اختيارية للطالب</span>
                    <input type="text" class="admin-control" wire:model="issueStudentNote" maxlength="500" placeholder="مثال: مبارك إتمام البرنامج بنجاح" @disabled(! $issueStudentId)>
                    @error('issueStudentNote')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                </label>
            </div>

            <footer class="cert-issue-footer">
                <span class="cert-issue-footer__hint">
                    <i class="fa-solid fa-shield-halved"></i>
                    عند ترك اسم البرنامج فارغاً يُستخدم اسم برنامج الدفعة، وتُلتقط نسخة ثابتة من القالب والبيانات وقت الإصدار.
                </span>
                <button type="button" class="cert-issue-submit" wire:click="issueCertificate" wire:loading.attr="disabled" wire:target="issueCertificate" @disabled(! $issueStudentId)>
                    <span wire:loading.remove wire:target="issueCertificate"><i class="fa-solid fa-award"></i> إصدار الشهادة</span>
                    <span wire:loading wire:target="issueCertificate">جاري الإصدار...</span>
                </button>
            </footer>
        </div>
    </div>
</section>

@if ($showExternalUpload)
    <div class="cert-external-modal" role="presentation" wire:click.self="closeExternalUpload" @keydown.escape.window="$wire.closeExternalUpload()">
        <form wire:submit="uploadExternalCertificate" role="dialog" aria-modal="true" aria-labelledby="external-certificate-title">
            <header class="cert-external-modal__head">
                <div class="cert-external-modal__icon"><i class="fa-solid fa-building-columns"></i></div>
                <div>
                    <span>شهادات واعتمادات من خارج المنصة</span>
                    <h2 id="external-certificate-title">رفع شهادة خارجية</h2>
                    <p>أضف شهادة دولية أو مهنية واربط ملفها الأصلي بحساب الطالب.</p>
                </div>
                <button type="button" wire:click="closeExternalUpload" aria-label="إغلاق"><i class="fa-solid fa-xmark"></i></button>
            </header>

            <div class="cert-external-modal__body">
                <section class="cert-external-section">
                    <header><span>1</span><div><strong>الطالب المستفيد</strong><small>يجب أن يكون الطالب مرتبطاً بحساب مستخدم.</small></div></header>
                    <label class="cert-issue-search">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="search" wire:model.live.debounce.300ms="externalStudentSearch" placeholder="ابحث بالاسم أو الرقم الأكاديمي أو الهوية...">
                    </label>
                    <div class="cert-external-students">
                        @foreach ($this->externalStudentCandidates as $student)
                            <button type="button" class="{{ $externalStudentId === $student->id ? 'is-active' : '' }}" wire:click="chooseExternalStudent({{ $student->id }})">
                                <span>{{ mb_substr($student->name_ar, 0, 1) }}</span>
                                <div><strong>{{ $student->name_ar }}</strong><small>#{{ $student->academic_id ?? '—' }} · {{ $student->batch?->program?->name_ar ?? 'بدون برنامج' }}</small></div>
                                <i class="fa-solid fa-circle-check"></i>
                            </button>
                        @endforeach
                    </div>
                    @error('externalStudentId')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror

                    @if ($externalStudentId)
                        <div class="cert-external-learning">
                            <div class="cert-external-learning__head">
                                <i class="fa-solid fa-graduation-cap"></i>
                                <div><strong>اختر البرنامج أو الدورة المرتبطة بالشهادة</strong><small>تظهر هنا جميع البرامج الأكاديمية ودورات المتجر المشترك فيها الطالب.</small></div>
                            </div>
                            @forelse ($this->externalLearningOptions as $option)
                                <label class="cert-external-learning__option {{ $externalRelatedKey === $option['key'] ? 'is-active' : '' }}">
                                    <input type="radio" wire:model.live="externalRelatedKey" value="{{ $option['key'] }}">
                                    <span class="cert-external-learning__icon"><i class="fa-solid {{ $option['type'] === 'academic_program' ? 'fa-building-columns' : 'fa-laptop-file' }}"></i></span>
                                    <span class="cert-external-learning__info"><strong>{{ $option['name'] }}</strong><small>{{ $option['kind'] }}</small></span>
                                    <span class="cert-external-learning__status">{{ $option['status'] }}</span>
                                    <i class="fa-solid fa-circle-check cert-external-learning__check"></i>
                                </label>
                            @empty
                                <div class="cert-external-learning__empty"><i class="fa-solid fa-circle-info"></i> لا توجد برامج أو دورات مسجّلة لهذا الطالب.</div>
                            @endforelse
                            @error('externalRelatedKey')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                        </div>
                    @endif
                </section>

                <section class="cert-external-section">
                    <header><span>2</span><div><strong>بيانات الاعتماد</strong><small>اكتب البيانات كما تظهر في الشهادة الأصلية.</small></div></header>
                    <div class="cert-external-grid">
                        <label class="cert-issue-field">
                            <span><i class="fa-solid fa-award"></i> اسم الشهادة أو الاعتماد *</span>
                            <input type="text" class="admin-control" wire:model="externalTitle" maxlength="255" placeholder="مثال: PMP Certification">
                            @error('externalTitle')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                        </label>
                        <label class="cert-issue-field">
                            <span><i class="fa-solid fa-building-columns"></i> جهة الإصدار أو الاعتماد *</span>
                            <input type="text" class="admin-control" wire:model="externalIssuer" maxlength="255" placeholder="مثال: PMI">
                            @error('externalIssuer')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                        </label>
                        <label class="cert-issue-field">
                            <span><i class="fa-solid fa-fingerprint"></i> رقم الاعتماد</span>
                            <input type="text" class="admin-control" wire:model="externalCredentialId" maxlength="255" placeholder="Credential ID">
                            @error('externalCredentialId')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                        </label>
                        <label class="cert-issue-field">
                            <span><i class="fa-solid fa-link"></i> رابط التحقق لدى الجهة</span>
                            <input type="url" class="admin-control" wire:model="externalVerificationUrl" dir="ltr" placeholder="https://...">
                            @error('externalVerificationUrl')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                        </label>
                        <label class="cert-issue-field">
                            <span><i class="fa-regular fa-calendar-check"></i> تاريخ الإصدار *</span>
                            <input type="date" class="admin-control" wire:model="externalIssuedAt">
                            @error('externalIssuedAt')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                        </label>
                        <label class="cert-issue-field">
                            <span><i class="fa-regular fa-calendar-xmark"></i> تاريخ الانتهاء</span>
                            <input type="date" class="admin-control" wire:model="externalExpiresAt">
                            @error('externalExpiresAt')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                        </label>
                    </div>
                </section>

                <section class="cert-external-section">
                    <header><span>3</span><div><strong>الملف والتفاصيل</strong><small>PDF أو صورة، بحد أقصى 10 ميجابايت.</small></div></header>
                    <label class="cert-external-upload">
                        <input type="file" wire:model="externalFile" accept=".pdf,.jpg,.jpeg,.png,.webp">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        @if ($externalFile)
                            <strong>{{ $externalFile->getClientOriginalName() }}</strong>
                            <small>تم اختيار الملف — يمكنك تغييره بالضغط هنا.</small>
                        @else
                            <strong>اسحب ملف الشهادة أو اضغط للاختيار</strong>
                            <small>PDF, JPG, PNG, WEBP · الحد الأقصى 10 MB</small>
                        @endif
                        <em wire:loading wire:target="externalFile">جاري تجهيز الملف...</em>
                    </label>
                    @error('externalFile')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror

                    <div class="cert-external-visibility">
                        <div class="cert-external-learning__head">
                            <i class="fa-solid fa-eye"></i>
                            <div><strong>ظهور الشهادة في لوحة الطالب</strong><small>حدد متى يستطيع الطالب رؤية الشهادة وفتح ملفها.</small></div>
                        </div>
                        <div class="cert-external-visibility__choices">
                            <label class="{{ $externalVisibilityMode === 'immediate' ? 'is-active' : '' }}">
                                <input type="radio" wire:model.live="externalVisibilityMode" value="immediate">
                                <i class="fa-solid fa-eye"></i><span><strong>إظهار فوراً</strong><small>تظهر بمجرد حفظها</small></span>
                            </label>
                            <label class="{{ $externalVisibilityMode === 'hidden' ? 'is-active' : '' }}">
                                <input type="radio" wire:model.live="externalVisibilityMode" value="hidden">
                                <i class="fa-solid fa-eye-slash"></i><span><strong>إخفاء</strong><small>تبقى للإدارة فقط</small></span>
                            </label>
                            <label class="{{ $externalVisibilityMode === 'scheduled' ? 'is-active' : '' }}">
                                <input type="radio" wire:model.live="externalVisibilityMode" value="scheduled">
                                <i class="fa-regular fa-clock"></i><span><strong>جدولة</strong><small>تظهر في موعد محدد</small></span>
                            </label>
                        </div>
                        @if ($externalVisibilityMode === 'scheduled')
                            <label class="cert-issue-field cert-external-visible-at">
                                <span><i class="fa-regular fa-calendar-check"></i> تاريخ ووقت الظهور *</span>
                                <input type="datetime-local" class="admin-control" wire:model="externalVisibleFrom" min="{{ now()->format('Y-m-d\TH:i') }}">
                                @error('externalVisibleFrom')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                            </label>
                        @endif
                        @error('externalVisibilityMode')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                    </div>

                    <div class="cert-external-grid">
                        <label class="cert-issue-field">
                            <span><i class="fa-regular fa-message"></i> رسالة للطالب</span>
                            <input type="text" class="admin-control" wire:model="externalStudentNote" maxlength="500" placeholder="تظهر للطالب مع الشهادة">
                            @error('externalStudentNote')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                        </label>
                        <label class="cert-issue-field">
                            <span><i class="fa-regular fa-note-sticky"></i> ملاحظات إدارية</span>
                            <input type="text" class="admin-control" wire:model="externalNotes" maxlength="1000" placeholder="لا تظهر للطالب">
                            @error('externalNotes')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                        </label>
                    </div>
                </section>
            </div>

            <footer class="cert-external-modal__footer">
                <span><i class="fa-solid fa-lock"></i> يُحفظ الملف في تخزين خاص ولا يُفتح إلا بعد التحقق من الصلاحية.</span>
                <div>
                    <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="closeExternalUpload">إلغاء</button>
                    <button type="submit" class="cert-issue-submit" wire:loading.attr="disabled" wire:target="uploadExternalCertificate,externalFile">
                        <span wire:loading.remove wire:target="uploadExternalCertificate"><i class="fa-solid fa-cloud-arrow-up"></i> حفظ وربط الشهادة</span>
                        <span wire:loading wire:target="uploadExternalCertificate">جاري الحفظ...</span>
                    </button>
                </div>
            </footer>
        </form>
    </div>
@endif

@if ($this->detailsCertificate)
    @php($details = $this->detailsCertificate)
    <div class="cert-details-modal" role="presentation" wire:click.self="closeDetails" @keydown.escape.window="$wire.closeDetails()">
        <section role="dialog" aria-modal="true" aria-labelledby="certificate-details-title">
            <header class="cert-details-modal__head">
                <div>
                    <span>بطاقة الشهادة</span>
                    <h2 id="certificate-details-title">{{ $details->holder_name }}</h2>
                </div>
                <button type="button" wire:click="closeDetails" aria-label="إغلاق"><i class="fa-solid fa-xmark"></i></button>
            </header>

            <div class="cert-details-modal__status">
                @if ($details->status === 'revoked')
                    <span class="cert-status-badge is-revoked"><i class="fa-solid fa-ban"></i> ملغاة</span>
                @elseif (! $details->hasValidIntegrity())
                    <span class="cert-status-badge is-tampered"><i class="fa-solid fa-triangle-exclamation"></i> بيانات معدّلة</span>
                @else
                    <span class="cert-status-badge is-valid"><i class="fa-solid fa-circle-check"></i> سارية وموثقة</span>
                @endif
                <code dir="ltr">{{ $details->code }}</code>
            </div>

            <dl class="cert-details-modal__grid">
                <div><dt>{{ $details->isExternal() ? 'الشهادة أو الاعتماد' : 'البرنامج' }}</dt><dd>{{ $details->program_name }}</dd></div>
                @if ($details->isExternal())
                    <div><dt>جهة الإصدار</dt><dd>{{ $details->external_issuer }}</dd></div>
                    <div><dt>رقم الاعتماد</dt><dd dir="ltr">{{ $details->external_credential_id ?: '—' }}</dd></div>
                    <div><dt>البرنامج المرتبط</dt><dd>{{ $details->related_program_name ?: '—' }}</dd></div>
                    <div><dt>نوع المصدر</dt><dd>شهادة خارجية مرفوعة</dd></div>
                @endif
                <div><dt>تاريخ الإصدار</dt><dd dir="ltr">{{ $details->issued_at->format('Y-m-d') }}</dd></div>
                @if (! $details->isExternal())
                    <div><dt>فترة البرنامج</dt><dd dir="ltr">{{ $details->program_started_at?->format('Y-m-d') ?? '—' }} → {{ $details->program_ended_at?->format('Y-m-d') ?? '—' }}</dd></div>
                    <div><dt>القالب</dt><dd>{{ $details->template?->name ?? 'قالب النظام' }}{{ $details->template_version ? ' — v'.$details->template_version : '' }}</dd></div>
                @endif
                <div><dt>أصدرها</dt><dd>{{ $details->issuer?->name ?? '—' }}</dd></div>
                <div><dt>الرقم الأكاديمي</dt><dd dir="ltr">{{ $details->academicStudent?->academic_id ?? '—' }}</dd></div>
                <div><dt>{{ $details->isExternal() ? 'الملف الأصلي' : 'ملف PDF' }}</dt><dd>{{ $details->pdf_path ? 'جاهز — '.($details->pdf_generated_at?->format('Y-m-d H:i') ?? '') : 'غير مُنشأ' }}</dd></div>
                <div><dt>سلامة البيانات</dt><dd>{{ $details->hasValidIntegrity() ? 'مطابقة للبصمة الأصلية' : 'لا تطابق البصمة الأصلية!' }}</dd></div>
                <div><dt>الظهور للطالب</dt><dd>{{ CertificateAccessPolicy::isVisible($details) ? 'ظاهرة الآن' : (CertificateAccessPolicy::pendingReason($details) ?? 'مخفية') }}</dd></div>
                <div><dt>السياسة المطبقة</dt><dd>
                    @if ($details->isExternal())
                        {{ match ($details->visibility_mode) { 'hidden' => 'مخفية عن الطالب', 'scheduled' => 'مجدولة — '.($details->visible_from?->format('Y-m-d H:i') ?? 'لم يحدد الموعد'), default => 'إظهار فوري' } }}
                    @else
                        {{ match (CertificateAccessSettings::defaultVisibilityMode()) { 'immediate' => 'فور الإصدار', 'after_exam_pass' => 'بعد اجتياز الاختبار', 'after_graduation_and_exam' => 'بعد التخرج واجتياز الاختبار', default => 'بعد اعتماد التخرج' } }}
                    @endif
                </dd></div>
            </dl>

            @if ($details->status === 'revoked' && $details->revocation_reason)
                <div class="cert-details-modal__revocation">
                    <strong><i class="fa-solid fa-ban"></i> سبب الإلغاء @if($details->revoked_at) — {{ $details->revoked_at->format('Y-m-d') }} @endif</strong>
                    <p>{{ $details->revocation_reason }}</p>
                </div>
            @endif

            <div class="cert-details-modal__verify">
                <span>رابط التحقق العام</span>
                <div>
                    <input type="text" readonly dir="ltr" value="{{ $details->verifyUrl() }}" onclick="this.select()">
                    <button type="button" class="cert-action js-copy-url" data-copy-url="{{ $details->verifyUrl() }}"><i class="fa-regular fa-copy"></i><span>نسخ</span></button>
                </div>
            </div>

            <footer class="cert-details-modal__actions">
                <a href="{{ route('admin.certificates.download', $details) }}" class="cert-action cert-action--primary"><i class="fa-solid fa-file-arrow-down"></i><span>{{ $details->isExternal() ? 'تنزيل الملف' : 'تنزيل PDF' }}</span></a>
                @if ($details->isExternal() && $details->external_verification_url)
                    <a href="{{ $details->external_verification_url }}" target="_blank" rel="noopener noreferrer" class="cert-action"><i class="fa-solid fa-building-columns"></i><span>تحقق لدى الجهة</span></a>
                @endif
                <a href="{{ $details->verifyUrl() }}" target="_blank" rel="noopener" class="cert-action"><i class="fa-solid fa-arrow-up-right-from-square"></i><span>صفحة التحقق</span></a>
                @if ($details->isValid())
                    <button type="button" class="cert-action cert-action--danger" wire:click="openRevoke({{ $details->id }})"><i class="fa-solid fa-ban"></i><span>إلغاء الصلاحية</span></button>
                @endif
            </footer>
        </section>
    </div>
@endif

@if ($revokeCertificateId)
    <div class="cert-revoke-modal" role="presentation" wire:click.self="cancelRevoke">
        <section role="dialog" aria-modal="true" aria-labelledby="revoke-certificate-title">
            <div class="cert-revoke-modal__icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <h2 id="revoke-certificate-title">إلغاء صلاحية الشهادة</h2>
            <p>سيظهر فوراً في صفحة التحقق أن الشهادة ملغاة. احتفظ بسبب واضح لسجل التدقيق.</p>
            <div class="admin-field">
                <label for="revocation-reason">سبب الإلغاء</label>
                <textarea id="revocation-reason" class="admin-control" rows="4" wire:model="revocationReason" placeholder="مثال: صدرت الشهادة ببيانات غير صحيحة وسيتم استبدالها..."></textarea>
                @error('revocationReason')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
            </div>
            <div class="admin-filter-actions">
                <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="cancelRevoke">تراجع</button>
                <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="confirmRevoke">تأكيد الإلغاء</button>
            </div>
        </section>
    </div>
@endif

@push('scripts')
<script>
    document.addEventListener('click', (event) => {
        const btn = event.target.closest('.js-copy-url');
        if (!btn) return;

        const url = btn.getAttribute('data-copy-url');
        if (!url) return;

        navigator.clipboard.writeText(url).then(() => {
            const old = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-check"></i><span>تم</span>';
            setTimeout(() => { btn.innerHTML = old; }, 1200);
        }).catch(() => {
            alert('تعذّر النسخ. انسخ الرابط يدوياً من الرابط المعروض.');
        });
    });
</script>
@endpush

@push('styles')
<style>
    .cert-admin-hero{position:relative;display:flex;align-items:center;justify-content:space-between;gap:1.5rem;overflow:hidden;margin-bottom:1rem;padding:1.55rem 1.65rem;border-radius:22px;background:radial-gradient(34rem 20rem at 100% 0,rgba(250,204,21,.2),transparent 55%),linear-gradient(135deg,#0d3325,#16603e);color:#fff;box-shadow:0 18px 42px rgba(13,51,37,.2)}.cert-admin-hero::after{content:"";position:absolute;inset:auto -3rem -7rem auto;width:16rem;height:16rem;border:1px solid rgba(255,255,255,.08);border-radius:50%}.cert-admin-hero__content,.cert-admin-hero__actions{position:relative;z-index:1}.cert-admin-hero__eyebrow{display:inline-flex;align-items:center;gap:.4rem;color:#fde68a;font-size:.66rem;font-weight:900;letter-spacing:.03em}.cert-admin-hero h1{margin:.3rem 0 .4rem;color:#fff;font-size:1.45rem;font-weight:900}.cert-admin-hero p{max-width:44rem;margin:0;color:rgba(255,255,255,.76);font-size:.75rem;line-height:1.85}.cert-admin-hero__actions{display:flex;gap:.55rem;flex-wrap:wrap}.cert-admin-hero__actions a,.cert-admin-hero__actions button{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;padding:.65rem .85rem;border:1px solid rgba(255,255,255,.24);border-radius:10px;background:rgba(255,255,255,.1);color:#fff;font:900 .68rem/1 inherit;text-decoration:none;cursor:pointer;backdrop-filter:blur(5px)}.cert-admin-hero__actions .cert-admin-hero__issue{border-color:#facc15;background:#facc15;color:#423407}
    .cert-admin-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.8rem;margin-bottom:1rem}.cert-admin-stats article{display:flex;align-items:center;gap:.8rem;padding:1rem;border:1px solid #e2e8f0;border-radius:15px;background:#fff;box-shadow:0 5px 18px rgba(15,23,42,.035)}.cert-admin-stats__icon{display:grid;place-items:center;flex:0 0 auto;width:2.8rem;height:2.8rem;border-radius:12px;font-size:.95rem}.cert-admin-stats__icon.is-total{background:#eff6ff;color:#2563eb}.cert-admin-stats__icon.is-active{background:#ecfdf5;color:#15803d}.cert-admin-stats__icon.is-revoked{background:#fef2f2;color:#dc2626}.cert-admin-stats__icon.is-month{background:#fefce8;color:#a16207}.cert-admin-stats span,.cert-admin-stats strong,.cert-admin-stats small{display:block}.cert-admin-stats span{color:#64748b;font-size:.62rem;font-weight:800}.cert-admin-stats strong{margin:.08rem 0;color:#17251f;font-size:1.25rem}.cert-admin-stats small{color:#94a3b8;font-size:.55rem}
    .cert-policy-panel{margin-bottom:1rem;border:1px solid #dce7e1;border-radius:17px;background:#fff;box-shadow:0 7px 24px rgba(15,23,42,.04);overflow:hidden}.cert-policy-panel>summary{display:flex;align-items:center;gap:.75rem;padding:1rem 1.15rem;cursor:pointer;list-style:none}.cert-policy-panel>summary::-webkit-details-marker{display:none}.cert-policy-panel__icon{display:grid;place-items:center;flex:0 0 auto;width:2.5rem;height:2.5rem;border-radius:11px;background:#ecfdf5;color:#15803d}.cert-policy-panel>summary>span:nth-child(2){display:grid;gap:.15rem;flex:1}.cert-policy-panel>summary strong{color:#17251f;font-size:.78rem}.cert-policy-panel>summary small{color:#64748b;font-size:.6rem}.cert-policy-panel__summary-state{color:#94a3b8;transition:transform .2s}.cert-policy-panel[open] .cert-policy-panel__summary-state{transform:rotate(180deg)}.cert-policy-panel form{padding:0 1.15rem 1.15rem;border-top:1px solid #eef2f0}.cert-policy-intro{display:flex;align-items:flex-start;gap:.6rem;margin:.9rem 0;padding:.7rem .8rem;border-radius:11px;background:#eff6ff;color:#1e40af}.cert-policy-intro>i{margin-top:.15rem}.cert-policy-intro strong{font-size:.65rem}.cert-policy-intro p{margin:.15rem 0 0;font-size:.6rem;line-height:1.7}.cert-policy-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.65rem}.cert-policy-select,.cert-policy-toggle{box-sizing:border-box;border:1px solid #e2e8f0;border-radius:11px;background:#f8fafc}.cert-policy-select{display:grid;gap:.4rem;padding:.75rem}.cert-policy-select>span{color:#334155;font-size:.64rem;font-weight:900}.cert-policy-select>span i{margin-inline-end:.25rem;color:#16a34a}.cert-policy-select select{width:100%;padding:.45rem;border:1px solid #d7e1dc;border-radius:7px;background:#fff;color:#334155;font:700 .62rem/1 inherit}.cert-policy-select small{color:#94a3b8;font-size:.52rem;line-height:1.6}.cert-policy-toggle{position:relative;display:flex;align-items:flex-start;gap:.6rem;padding:.75rem;cursor:pointer}.cert-policy-toggle input{position:absolute;opacity:0}.cert-policy-toggle__switch{position:relative;flex:0 0 auto;width:2rem;height:1.08rem;margin-top:.1rem;border-radius:999px;background:#cbd5e1;transition:background .2s}.cert-policy-toggle__switch::after{content:"";position:absolute;top:.15rem;right:.15rem;width:.78rem;height:.78rem;border-radius:50%;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.2);transition:transform .2s}.cert-policy-toggle input:checked+.cert-policy-toggle__switch{background:#16a34a}.cert-policy-toggle input:checked+.cert-policy-toggle__switch::after{transform:translateX(-.92rem)}.cert-policy-toggle>span:last-child{display:grid;gap:.15rem}.cert-policy-toggle strong{color:#334155;font-size:.63rem}.cert-policy-toggle small{color:#94a3b8;font-size:.52rem;line-height:1.5}.cert-policy-panel__footer{display:flex;align-items:center;justify-content:space-between;gap:.75rem;margin-top:.85rem;padding-top:.8rem;border-top:1px solid #eef2f0}.cert-policy-panel__footer>span{color:#64748b;font-size:.56rem}.cert-policy-panel__footer>span i{color:#16a34a}.cert-policy-panel__footer button{display:inline-flex;align-items:center;gap:.35rem;padding:.55rem .75rem;border:0;border-radius:8px;background:#166534;color:#fff;font:900 .63rem/1 inherit;cursor:pointer}
    .cert-auto-flow{display:flex;align-items:center;gap:.45rem;margin:0 0 .9rem;padding:.7rem;border:1px solid #dcfce7;border-radius:12px;background:#f7fdf9}.cert-auto-flow>article{position:relative;display:flex;align-items:center;gap:.45rem;min-width:0;flex:1}.cert-auto-flow>article>span{position:absolute;top:-.35rem;right:-.2rem;display:grid;place-items:center;width:1rem;height:1rem;border-radius:50%;background:#166534;color:#fff;font-size:.45rem;font-weight:900}.cert-auto-flow>article>i{display:grid;place-items:center;flex:0 0 auto;width:1.9rem;height:1.9rem;border-radius:8px;background:#dcfce7;color:#15803d;font-size:.7rem}.cert-auto-flow>article>div{display:grid;gap:.08rem}.cert-auto-flow strong{color:#334155;font-size:.58rem}.cert-auto-flow small{color:#94a3b8;font-size:.48rem}.cert-auto-flow>i{color:#86efac;font-size:.55rem}.cert-policy-toggle--featured{border-color:#bbf7d0;background:#f0fdf4}
    .cert-management-panel,.cert-issue-studio{overflow:hidden;border-color:#dce7e1!important;border-radius:18px!important;box-shadow:0 8px 26px rgba(15,23,42,.045);width:100%}.cert-section-kicker{display:block;margin-bottom:.15rem;color:#16a34a;font-size:.58rem;font-weight:900}
    .cert-filter-panel{display:grid;gap:.85rem;width:auto;margin:.25rem 1rem 0;padding:1rem 1.1rem;border:1px solid #e2e8f0;border-radius:14px;background:linear-gradient(180deg,#f8fafc,#fff)}
    .cert-filter-panel__main{display:flex;gap:.75rem;align-items:center;flex-wrap:wrap}
    .cert-filter-search{display:flex;align-items:center;gap:.55rem;flex:1;min-width:16rem;padding:0 .85rem;border:1px solid #dbe4df;border-radius:12px;background:#fff;transition:border-color .15s,box-shadow .15s}
    .cert-filter-search:focus-within{border-color:#16a34a;box-shadow:0 0 0 3px rgba(22,163,74,.12)}
    .cert-filter-search>i{color:#16a34a;font-size:.78rem}
    .cert-filter-search input{flex:1;min-width:0;padding:.62rem 0;border:0;background:transparent;color:#17251f;font-size:.72rem;outline:none}
    .cert-filter-search kbd{flex:0 0 auto;padding:.18rem .5rem;border:1px solid #e2e8f0;border-radius:999px;background:#f8fafc;color:#94a3b8;font:800 .52rem/1 inherit}
    .cert-filter-search:focus-within kbd{border-color:#bbf7d0;background:#f0fdf4;color:#16a34a}
    .cert-filter-panel__row{display:flex;gap:.65rem;align-items:flex-end;flex-wrap:wrap}
    .cert-filter-field{display:grid;gap:.32rem;flex:1;min-width:11rem}
    .cert-filter-field>span{color:#475569;font-size:.6rem;font-weight:900}
    .cert-filter-field>span i{margin-inline-end:.3rem;color:#16a34a}
    .cert-filter-field input,.cert-filter-field select{width:100%;box-sizing:border-box;padding:.52rem .6rem;border:1px solid #dbe4df;border-radius:9px;background:#fff;color:#334155;font:700 .66rem/1.2 inherit;transition:border-color .15s,box-shadow .15s}
    .cert-filter-field input:focus,.cert-filter-field select:focus{border-color:#16a34a;outline:none;box-shadow:0 0 0 3px rgba(22,163,74,.1)}
    .cert-filter-field--range{flex:1.5;min-width:15rem}
    .cert-filter-range{display:flex;align-items:center;gap:.45rem}
    .cert-filter-range input{flex:1;min-width:0}
    .cert-filter-range>i{flex:0 0 auto;color:#94a3b8;font-size:.58rem}
    .cert-filter-field--sort{flex:0 1 10.5rem;min-width:9rem}
    .cert-clear-filters{display:inline-flex;align-items:center;justify-content:center;gap:.35rem;flex:0 0 auto;min-height:2.35rem;padding:.5rem .85rem;border:1px solid #d7e1dc;border-radius:9px;background:#fff;color:#475569;font:800 .65rem/1 inherit;cursor:pointer;transition:border-color .15s,color .15s,background .15s}.cert-clear-filters:hover{border-color:#86efac;background:#f0fdf4;color:#166534}
    .cert-results-toolbar{display:flex;align-items:center;justify-content:space-between;gap:.75rem;margin:.85rem 1rem 0;padding:.55rem .8rem;border:1px solid #e2e8f0;border-radius:11px;background:#fff}.cert-results-toolbar__summary{display:flex;align-items:center;gap:.45rem;color:#64748b;font-size:.64rem}.cert-results-toolbar__summary i{color:#16a34a}.cert-results-toolbar__summary strong{color:#17251f}.cert-results-toolbar__filtered{display:inline-flex;align-items:center;gap:.3rem;padding:.2rem .5rem;border-radius:999px;background:#eff6ff;color:#1d4ed8;font-size:.56rem;font-style:normal;font-weight:800}.cert-results-toolbar__perpage{display:flex;align-items:center;gap:.4rem;color:#64748b;font-size:.62rem;font-weight:800}.cert-results-toolbar__perpage select{padding:.3rem .45rem;border:1px solid #dbe4df;border-radius:7px;background:#fff;color:#334155;font:800 .62rem/1 inherit}
    .cert-table-wrap{margin:1rem;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;box-shadow:0 4px 16px rgba(15,23,42,.035);transition:opacity .15s}.cert-table-wrap.is-loading{opacity:.45;pointer-events:none}.cert-table-wrap .admin-table{margin:0;width:100%}
    .cert-table{width:100%;table-layout:fixed;border-collapse:collapse}
    .cert-col-code{width:13%}
    .cert-col-holder{width:17%}
    .cert-col-program{width:20%}
    .cert-col-issued{width:11%}
    .cert-col-status{width:12%}
    .cert-col-actions{width:27%}
    .cert-table thead th{padding:.7rem .85rem;border-bottom:1px solid #e2e8f0;background:#f8fafc;color:#64748b;font-size:.57rem;font-weight:900;letter-spacing:.02em;white-space:nowrap;text-align:start}
    .cert-table tbody td{padding:.8rem .85rem;border-bottom:1px solid #f1f5f9;vertical-align:middle;text-align:start;overflow:hidden}
    .cert-table tbody tr:last-child td{border-bottom:0}
    .cert-table tbody tr:nth-child(even){background:#fcfdfd}
    .cert-identity{display:grid;gap:.25rem;justify-items:start;min-width:0}.cert-identity small{display:inline-flex;align-items:center;gap:.25rem;max-width:100%;overflow:hidden;color:#94a3b8;font-size:.55rem;text-overflow:ellipsis;white-space:nowrap}.cert-identity small i{font-size:.5rem}
    .cert-program-cell{display:grid;gap:.2rem;min-width:0}.cert-program-cell small{color:#94a3b8;font-size:.55rem}
    .cert-issue-cell{display:grid;gap:.25rem;min-width:0}.cert-issue-cell small{display:inline-flex;align-items:center;gap:.25rem;color:#94a3b8;font-size:.55rem}
    .cert-status-badge{display:inline-flex;align-items:center;gap:.3rem;padding:.32rem .55rem;border-radius:999px;font-size:.6rem;font-weight:900;white-space:nowrap}.cert-status-badge.is-valid{background:#dcfce7;color:#166534}.cert-status-badge.is-revoked{background:#fee2e2;color:#b91c1c}.cert-status-badge.is-expired{background:#fef3c7;color:#92400e}.cert-status-badge.is-tampered{background:#ffe4e6;color:#9f1239}.cert-student-visibility{display:flex;align-items:center;gap:.25rem;margin-top:.3rem;font-size:.52rem;font-weight:800}.cert-student-visibility.is-visible{color:#15803d}.cert-student-visibility.is-hidden{color:#94a3b8}.cert-table-wrap tbody tr{transition:background .15s,box-shadow .15s}.cert-table-wrap tbody tr:hover{background:#f6fbf8!important;box-shadow:inset 3px 0 0 #16a34a}.cert-code{display:inline-block;max-width:100%;padding:.32rem .5rem;border:1px solid #dbeafe;border-radius:7px;background:#eff6ff;color:#1d4ed8;font-size:.62rem;font-weight:800;letter-spacing:.02em;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.cert-holder{display:flex;align-items:center;gap:.6rem;min-width:0}.cert-holder>span{display:grid;place-items:center;flex:0 0 auto;width:2.1rem;height:2.1rem;border-radius:50%;background:linear-gradient(135deg,#16a34a,#15803d);color:#fff;font-size:.72rem;font-weight:900;box-shadow:0 3px 8px rgba(22,163,74,.25)}.cert-holder strong{min-width:0;overflow:hidden;color:#263a31;font-size:.7rem;text-overflow:ellipsis;white-space:nowrap}.cert-program{display:block;min-width:0;overflow:hidden;color:#475569;font-size:.66rem;text-overflow:ellipsis;white-space:nowrap}.cert-date{display:flex;align-items:center;gap:.35rem;color:#64748b;font-size:.63rem;white-space:nowrap}.cert-date i{color:#94a3b8}.cert-action{display:inline-flex;align-items:center;gap:.3rem;padding:.42rem .55rem;border:1px solid #dbe4df;border-radius:8px;background:#fff;color:#475569;font:800 .58rem/1 inherit;text-decoration:none;cursor:pointer;transition:border-color .12s,background .12s,color .12s,transform .12s}.cert-action:hover{border-color:#86efac;background:#f0fdf4;color:#166534;transform:translateY(-1px)}.cert-action--primary{border-color:#166534;background:linear-gradient(135deg,#16a34a,#15803d);color:#fff;box-shadow:0 3px 8px rgba(22,163,74,.28)}.cert-action--primary:hover{background:#14532d;color:#fff}.cert-action--publish{border-color:#86efac;background:#f0fdf4;color:#166534}.cert-action--danger{border-color:#fecaca;color:#b91c1c}.cert-action--danger:hover{border-color:#f87171;background:#fef2f2;color:#991b1b}
    .cert-row-actions{display:flex;align-items:center;gap:.35rem;flex-wrap:wrap;justify-content:flex-start;max-width:100%}
    .cert-row-actions .cert-action{flex:0 0 auto}
    .cert-action--details{border-color:#dbeafe;background:#f8fbff;color:#1d4ed8}.cert-action--details:hover{border-color:#93c5fd;background:#eff6ff;color:#1e40af}
    .cert-action--icon{justify-content:center;width:1.95rem;height:1.95rem;padding:0}
    .cert-action--icon span{display:none}
    .cert-action-group{display:inline-flex;align-items:center;gap:.25rem;padding:.2rem;border:1px dashed #dbe4df;border-radius:10px;background:#fbfdfc}
    .cert-action--icon{width:1.8rem;height:1.8rem}.cert-empty-state{display:grid;place-items:center;gap:.3rem;padding:2.4rem;color:#94a3b8}.cert-empty-state i{font-size:1.6rem}.cert-empty-state strong{color:#475569;font-size:.75rem}.cert-empty-state span{font-size:.62rem}
    .cert-issue-studio{margin-top:1rem!important}
    .cert-issue-stepper{display:flex;align-items:center;gap:.5rem;margin:0;padding:0;list-style:none}
    .cert-issue-stepper li{display:inline-flex;align-items:center;gap:.4rem;padding:.4rem .7rem;border:1px solid #e2e8f0;border-radius:999px;background:#fff;color:#94a3b8;font-size:.6rem;font-weight:900;white-space:nowrap;transition:border-color .15s,background .15s,color .15s}
    .cert-issue-stepper li span{display:grid;place-items:center;width:1.25rem;height:1.25rem;border-radius:50%;background:#f1f5f9;color:#94a3b8;font-size:.55rem}
    .cert-issue-stepper li.is-active{border-color:#bbf7d0;background:#f0fdf4;color:#166534}
    .cert-issue-stepper li.is-active span{background:#16a34a;color:#fff}
    .cert-issue-layout{display:grid;grid-template-columns:minmax(300px,.9fr) minmax(380px,1.1fr);gap:1rem;align-items:start;padding:0 1rem 1rem}
    .cert-issue-panel{display:flex;flex-direction:column;gap:.85rem;padding:1.05rem;border:1px solid #e2e8f0;border-radius:15px;background:#fff;box-shadow:0 5px 16px rgba(15,23,42,.04);transition:opacity .2s}
    .cert-issue-panel.is-waiting{opacity:.72}
    .cert-issue-panel__head{display:flex;align-items:center;gap:.65rem;padding-bottom:.75rem;border-bottom:1px dashed #e2e8f0}
    .cert-issue-panel__step{display:grid;place-items:center;flex:0 0 auto;width:2rem;height:2rem;border-radius:10px;background:linear-gradient(135deg,#16a34a,#15803d);color:#fff;font-size:.75rem;font-weight:900;box-shadow:0 3px 8px rgba(22,163,74,.25)}
    .cert-issue-panel__head strong{display:block;color:#17251f;font-size:.76rem}
    .cert-issue-panel__head small{display:block;color:#94a3b8;font-size:.57rem}
    .cert-issue-search{display:flex;align-items:center;gap:.5rem;padding:0 .75rem;border:1px solid #dbe4df;border-radius:11px;background:#f8fafc;transition:border-color .15s,box-shadow .15s,background .15s}
    .cert-issue-search:focus-within{border-color:#16a34a;background:#fff;box-shadow:0 0 0 3px rgba(22,163,74,.12)}
    .cert-issue-search i{color:#16a34a;font-size:.72rem}
    .cert-issue-search input{flex:1;min-width:0;padding:.58rem 0;border:0;background:transparent;color:#17251f;font-size:.68rem;outline:none}
    .cert-issue-candidates__empty{display:grid;place-items:center;gap:.35rem;padding:1.6rem;color:#94a3b8;font-size:.62rem}
    .cert-issue-candidates__empty i{font-size:1.1rem}
    .cert-issue-selected{display:flex;align-items:center;gap:.5rem;padding:.6rem .75rem;border:1px solid #bbf7d0;border-radius:11px;background:#f0fdf4;color:#166534;font-size:.63rem;font-weight:800}
    .cert-issue-selected>i{color:#16a34a}
    .cert-issue-selected code{padding:.1rem .4rem;border-radius:6px;background:#dcfce7;font-size:.6rem}
    .cert-issue-selected button{display:inline-flex;align-items:center;gap:.3rem;margin-inline-start:auto;padding:.35rem .6rem;border:1px solid #d7e1dc;border-radius:8px;background:#fff;color:#64748b;font:800 .58rem/1 inherit;cursor:pointer;transition:border-color .12s,color .12s}
    .cert-issue-selected button:hover{border-color:#fca5a5;color:#b91c1c}
    .cert-issue-summary{display:flex;align-items:center;gap:.7rem;padding:.75rem .85rem;border:1px solid #d1fae5;border-radius:12px;background:linear-gradient(135deg,#f0fdf4,#ecfdf5)}
    .cert-issue-summary__avatar{display:grid;place-items:center;flex:0 0 auto;width:2.5rem;height:2.5rem;border-radius:50%;background:linear-gradient(135deg,#16a34a,#15803d);color:#fff;font-size:.85rem;font-weight:900;box-shadow:0 4px 10px rgba(22,163,74,.28)}
    .cert-issue-summary__info{display:grid;gap:.12rem;min-width:0;flex:1}
    .cert-issue-summary__info strong{overflow:hidden;color:#14532d;font-size:.74rem;text-overflow:ellipsis;white-space:nowrap}
    .cert-issue-summary__info small{overflow:hidden;color:#4d7c62;font-size:.58rem;text-overflow:ellipsis;white-space:nowrap}
    .cert-issue-summary__tags{display:flex;gap:.35rem;flex-wrap:wrap;justify-content:flex-end}
    .cert-issue-summary__tags span{padding:.25rem .55rem;border:1px solid #bbf7d0;border-radius:999px;background:#fff;color:#166534;font-size:.55rem;font-weight:900}
    .cert-issue-summary__tags span.is-status{border-color:#a7f3d0;background:#d1fae5}
    .cert-issue-summary--empty{justify-content:center;gap:.5rem;border-style:dashed;border-color:#e2e8f0;background:#f8fafc;color:#94a3b8;font-size:.63rem}
    .cert-issue-summary--empty i{font-size:.95rem}
    .cert-issue-fields{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.7rem}
    .cert-issue-field{display:grid;gap:.32rem;min-width:0}
    .cert-issue-field--full{grid-column:1/-1}
    .cert-issue-field>span{color:#475569;font-size:.6rem;font-weight:900}
    .cert-issue-field>span i{margin-inline-end:.3rem;color:#16a34a}
    .cert-issue-field .admin-control{width:100%;box-sizing:border-box}
    .cert-issue-field .admin-control:disabled{background:#f1f5f9;color:#94a3b8;cursor:not-allowed}
    .cert-issue-footer{display:flex;align-items:center;justify-content:space-between;gap:.85rem;padding-top:.85rem;border-top:1px dashed #e2e8f0}
    .cert-issue-footer__hint{display:flex;align-items:flex-start;gap:.4rem;color:#94a3b8;font-size:.56rem;line-height:1.7}
    .cert-issue-footer__hint i{margin-top:.15rem;color:#16a34a}
    .cert-issue-submit{display:inline-flex;align-items:center;justify-content:center;gap:.45rem;flex:0 0 auto;padding:.65rem 1.4rem;border:0;border-radius:11px;background:linear-gradient(135deg,#16a34a,#15803d);color:#fff;font:900 .72rem/1 inherit;cursor:pointer;box-shadow:0 6px 16px rgba(22,163,74,.3);transition:transform .15s,box-shadow .15s,opacity .15s}
    .cert-issue-submit:hover:not(:disabled){transform:translateY(-1px);box-shadow:0 8px 22px rgba(22,163,74,.4)}
    .cert-issue-submit:disabled{opacity:.55;box-shadow:none;cursor:not-allowed}
    .cert-issue-access{display:grid;gap:.65rem;margin-top:.9rem;padding:.8rem;border:1px solid #dbeafe;border-radius:12px;background:#f8fbff}.cert-issue-access__head{display:flex;align-items:center;gap:.55rem}.cert-issue-access__head>i{display:grid;place-items:center;width:2rem;height:2rem;border-radius:9px;background:#dbeafe;color:#1d4ed8}.cert-issue-access__head>div{display:grid;gap:.1rem}.cert-issue-access__head strong{color:#1e3a5f;font-size:.65rem}.cert-issue-access__head small{color:#64748b;font-size:.53rem}.cert-issue-access__grid{display:grid;grid-template-columns:1fr 1fr;gap:.55rem}.cert-issue-access__permissions{display:flex;gap:.45rem;flex-wrap:wrap}.cert-issue-access__permissions label{display:flex;align-items:center;gap:.3rem;padding:.35rem .55rem;border:1px solid #dbeafe;border-radius:999px;background:#fff;color:#475569;font-size:.57rem;font-weight:800;cursor:pointer}.cert-issue-access__permissions input{accent-color:#16a34a}
    .admin-row-actions{display:flex;flex-wrap:wrap;gap:.35rem;align-items:center}

    .cert-status-chips{display:flex;flex-wrap:wrap;gap:.5rem}
    .cert-status-chip{
        border:1px solid #e2e8f0;
        background:#fff;
        color:#334155;
        border-radius:999px;
        padding:.5rem .85rem;
        font-size:.68rem;
        font-weight:900;
        cursor:pointer;
        display:inline-flex;
        align-items:center;
        gap:.45rem;
        transition:border-color .12s, background .12s, box-shadow .12s, transform .12s;
    }
    .cert-status-chip:hover{transform:translateY(-1px)}
    .cert-status-chip>i{font-size:.62rem;color:#94a3b8}
    .cert-status-chip--active>i{color:#16a34a}
    .cert-status-chip--revoked>i{color:#dc2626}
    .cert-status-chip__count{
        background:rgba(15,23,42,.06);
        border-radius:999px;
        padding:.12rem .5rem;
        font-size:.62rem;
        font-weight:900;
    }
    .cert-status-chip.is-active{
        border-color:#0f766e;
        background:#ecfdf5;
        box-shadow:0 0 0 3px rgba(15,118,110,.12);
    }
    .cert-status-chip.is-active>i{color:#0f766e}
    .cert-status-chip.is-active .cert-status-chip__count{background:rgba(15,118,110,.14);color:#0f766e}
    .cert-status-chip--active.is-active{
        border-color:#166534;background:#dcfce7;
    }
    .cert-status-chip--active.is-active .cert-status-chip__count{background:rgba(22,101,52,.16);color:#166534}
    .cert-status-chip--revoked.is-active{
        border-color:#b91c1c;background:#fef2f2;
    }
    .cert-status-chip--revoked.is-active>i{color:#b91c1c}
    .cert-status-chip--revoked.is-active .cert-status-chip__count{background:rgba(185,28,28,.12);color:#b91c1c}

    .cert-issue-candidates{
        max-height: 320px;
        overflow: auto;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: .5rem;
        background:#f8fafc;
    }
    .cert-issue-candidates::-webkit-scrollbar{width:6px}
    .cert-issue-candidates::-webkit-scrollbar-thumb{border-radius:999px;background:#cbd5e1}
    .cert-candidate-row{display:flex;align-items:center;gap:.6rem;width:100%;margin-bottom:.35rem;padding:.55rem .6rem;border:1px solid #eef2f0;border-radius:10px;background:#fff;text-align:start;cursor:pointer;transition:border-color .12s,background .12s}
    .cert-candidate-row:hover{border-color:#bbf7d0;background:#f7fdf9}
    .cert-candidate-row.is-active{border-color:#16a34a;background:#ecfdf5;box-shadow:0 0 0 3px rgba(22,163,74,.1)}
    .cert-candidate-row__avatar{display:grid;place-items:center;flex:0 0 auto;width:2rem;height:2rem;border-radius:50%;background:#f1f5f9;color:#475569;font-size:.7rem;font-weight:900}
    .cert-candidate-row.is-active .cert-candidate-row__avatar{background:#16a34a;color:#fff}
    .cert-candidate-row__info{display:grid;gap:.1rem;min-width:0;flex:1}
    .cert-candidate-row__info strong{color:#17251f;font-size:.68rem}
    .cert-candidate-row__info small{color:#94a3b8;font-size:.56rem}
    .cert-candidate-row__check{color:transparent;font-size:.85rem}
    .cert-candidate-row.is-active .cert-candidate-row__check{color:#16a34a}

    .cert-issue-candidate__meta{
        display:flex;
        flex-direction:column;
        align-items:flex-end;
        gap:.25rem;
    }
    .cert-issue-candidate__program{
        font-size:.78rem;
        color:#64748b;
        max-width: 10rem;
        white-space:nowrap;
        overflow:hidden;
        text-overflow:ellipsis;
    }
    .cert-issue-candidate__status{
        font-size:.72rem;
        font-weight:900;
        color:#0f766e;
        opacity:.95;
    }

    @media(max-width:800px){.cert-issue-fields{grid-template-columns:1fr}}
    .cert-details-modal{position:fixed;z-index:10400;inset:0;display:grid;place-items:center;padding:1rem;background:rgba(15,23,42,.6);backdrop-filter:blur(4px)}.cert-details-modal>section{width:min(560px,100%);max-height:92vh;overflow:auto;padding:1.3rem;border-radius:20px;background:#fff;box-shadow:0 30px 80px rgba(15,23,42,.35)}
    .cert-details-modal__head{display:flex;align-items:flex-start;justify-content:space-between;gap:.75rem;margin-bottom:.85rem}.cert-details-modal__head span{color:#16a34a;font-size:.58rem;font-weight:900}.cert-details-modal__head h2{margin:.15rem 0 0;color:#17251f;font-size:1.05rem;font-weight:900}.cert-details-modal__head button{display:grid;place-items:center;width:2rem;height:2rem;border:1px solid #e2e8f0;border-radius:9px;background:#fff;color:#64748b;cursor:pointer}
    .cert-details-modal__status{display:flex;align-items:center;justify-content:space-between;gap:.6rem;margin-bottom:.9rem;padding:.6rem .75rem;border:1px solid #e2e8f0;border-radius:11px;background:#f8fafc}.cert-details-modal__status code{padding:.28rem .5rem;border-radius:7px;background:#eff6ff;color:#1d4ed8;font-size:.63rem}
    .cert-details-modal__grid{display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin:0 0 .9rem}.cert-details-modal__grid div{padding:.55rem .65rem;border:1px solid #eef2f0;border-radius:10px;background:#fff}.cert-details-modal__grid dt{color:#94a3b8;font-size:.55rem;font-weight:800}.cert-details-modal__grid dd{margin:.18rem 0 0;color:#334155;font-size:.66rem;font-weight:800;overflow-wrap:anywhere}
    .cert-details-modal__revocation{margin-bottom:.9rem;padding:.7rem .8rem;border:1px solid #fecaca;border-radius:11px;background:#fef2f2}.cert-details-modal__revocation strong{display:flex;align-items:center;gap:.35rem;color:#b91c1c;font-size:.62rem}.cert-details-modal__revocation p{margin:.35rem 0 0;color:#7f1d1d;font-size:.63rem;line-height:1.7}
    .cert-details-modal__verify{margin-bottom:1rem}.cert-details-modal__verify>span{display:block;margin-bottom:.3rem;color:#64748b;font-size:.58rem;font-weight:800}.cert-details-modal__verify>div{display:flex;gap:.4rem}.cert-details-modal__verify input{min-width:0;flex:1;padding:.5rem .6rem;border:1px solid #dbe4df;border-radius:8px;background:#f8fafc;color:#475569;font-size:.6rem}
    .cert-details-modal__actions{display:flex;gap:.45rem;flex-wrap:wrap}
    .cert-revoke-modal{position:fixed;z-index:10500;inset:0;display:grid;place-items:center;padding:1rem;background:rgba(15,23,42,.62);backdrop-filter:blur(4px)}.cert-revoke-modal>section{width:min(460px,100%);padding:1.25rem;border-radius:18px;background:#fff;box-shadow:0 30px 80px rgba(15,23,42,.35)}.cert-revoke-modal__icon{display:grid;place-items:center;width:3rem;height:3rem;margin:0 auto .6rem;border-radius:50%;background:#fef3c7;color:#b45309;font-size:1.1rem}.cert-revoke-modal h2{margin:0;text-align:center;color:#17251f;font-size:1rem;font-weight:900}.cert-revoke-modal>section>p{margin:.35rem 0 1rem;text-align:center;color:#64748b;font-size:.7rem;line-height:1.7}
    .cert-source-external{color:#7c3aed!important}
    .cert-external-modal{position:fixed;z-index:10600;inset:0;display:grid;place-items:center;padding:1rem;background:rgba(15,23,42,.7);backdrop-filter:blur(5px)}
    .cert-external-modal>form{display:flex;flex-direction:column;width:min(920px,100%);max-height:94vh;overflow:hidden;border-radius:20px;background:#fff;box-shadow:0 32px 90px rgba(15,23,42,.4)}
    .cert-external-modal__head{display:flex;align-items:flex-start;gap:.8rem;padding:1.15rem 1.25rem;border-bottom:1px solid #e2e8f0;background:linear-gradient(135deg,#faf5ff,#fff)}
    .cert-external-modal__icon{display:grid;place-items:center;flex:0 0 auto;width:2.8rem;height:2.8rem;border-radius:13px;background:#ede9fe;color:#7c3aed;font-size:1.05rem}
    .cert-external-modal__head>div:nth-child(2){min-width:0;flex:1}
    .cert-external-modal__head span{color:#7c3aed;font-size:.57rem;font-weight:900}
    .cert-external-modal__head h2{margin:.12rem 0;color:#17251f;font-size:1.05rem;font-weight:900}
    .cert-external-modal__head p{margin:0;color:#64748b;font-size:.62rem}
    .cert-external-modal__head>button{display:grid;place-items:center;width:2rem;height:2rem;border:1px solid #e2e8f0;border-radius:9px;background:#fff;color:#64748b;cursor:pointer}
    .cert-external-modal__body{display:grid;gap:.8rem;overflow:auto;padding:1rem 1.25rem;background:#f8fafc}
    .cert-external-section{display:grid;gap:.75rem;padding:.9rem;border:1px solid #e2e8f0;border-radius:14px;background:#fff}
    .cert-external-section>header{display:flex;align-items:center;gap:.55rem}
    .cert-external-section>header>span{display:grid;place-items:center;width:1.7rem;height:1.7rem;border-radius:8px;background:#7c3aed;color:#fff;font-size:.62rem;font-weight:900}
    .cert-external-section>header strong{display:block;color:#334155;font-size:.7rem}
    .cert-external-section>header small{display:block;color:#94a3b8;font-size:.53rem}
    .cert-external-students{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.45rem;max-height:150px;overflow:auto}
    .cert-external-students button{display:flex;align-items:center;gap:.5rem;min-width:0;padding:.55rem;border:1px solid #e2e8f0;border-radius:10px;background:#fff;text-align:start;cursor:pointer}
    .cert-external-students button>span{display:grid;place-items:center;flex:0 0 auto;width:1.8rem;height:1.8rem;border-radius:50%;background:#f1f5f9;color:#64748b;font-size:.65rem;font-weight:900}
    .cert-external-students button>div{display:grid;min-width:0;flex:1}
    .cert-external-students button strong,.cert-external-students button small{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    .cert-external-students button strong{color:#334155;font-size:.62rem}.cert-external-students button small{color:#94a3b8;font-size:.5rem}
    .cert-external-students button>i{color:transparent}.cert-external-students button.is-active{border-color:#a78bfa;background:#f5f3ff}.cert-external-students button.is-active>span{background:#7c3aed;color:#fff}.cert-external-students button.is-active>i{color:#7c3aed}
    .cert-external-learning,.cert-external-visibility{display:grid;gap:.5rem;padding:.7rem;border:1px solid #ddd6fe;border-radius:12px;background:#fafaff}
    .cert-external-learning__head{display:flex;align-items:center;gap:.5rem}.cert-external-learning__head>i{display:grid;place-items:center;flex:0 0 auto;width:1.8rem;height:1.8rem;border-radius:8px;background:#ede9fe;color:#7c3aed;font-size:.68rem}.cert-external-learning__head strong,.cert-external-learning__head small{display:block}.cert-external-learning__head strong{color:#4c1d95;font-size:.62rem}.cert-external-learning__head small{color:#8b5cf6;font-size:.5rem}
    .cert-external-learning__option{display:flex;align-items:center;gap:.5rem;padding:.55rem .65rem;border:1px solid #e2e8f0;border-radius:10px;background:#fff;cursor:pointer;transition:border-color .12s,background .12s}.cert-external-learning__option:hover{border-color:#c4b5fd}.cert-external-learning__option>input{position:absolute;opacity:0}.cert-external-learning__option.is-active{border-color:#8b5cf6;background:#f5f3ff;box-shadow:0 0 0 2px rgba(139,92,246,.1)}.cert-external-learning__icon{display:grid;place-items:center;flex:0 0 auto;width:1.8rem;height:1.8rem;border-radius:8px;background:#f1f5f9;color:#64748b;font-size:.65rem}.is-active .cert-external-learning__icon{background:#7c3aed;color:#fff}.cert-external-learning__info{display:grid;min-width:0;flex:1}.cert-external-learning__info strong{overflow:hidden;color:#334155;font-size:.62rem;text-overflow:ellipsis;white-space:nowrap}.cert-external-learning__info small{color:#94a3b8;font-size:.5rem}.cert-external-learning__status{padding:.18rem .45rem;border-radius:999px;background:#ecfdf5;color:#047857;font-size:.48rem;font-weight:900}.cert-external-learning__check{color:transparent;font-size:.7rem}.is-active>.cert-external-learning__check{color:#7c3aed}.cert-external-learning__empty{padding:.55rem;border-radius:9px;background:#fff;color:#64748b;font-size:.55rem}
    .cert-external-visibility__choices{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.45rem}.cert-external-visibility__choices>label{display:flex;align-items:center;gap:.45rem;padding:.6rem;border:1px solid #e2e8f0;border-radius:10px;background:#fff;cursor:pointer}.cert-external-visibility__choices input{position:absolute;opacity:0}.cert-external-visibility__choices>label>i{display:grid;place-items:center;flex:0 0 auto;width:1.7rem;height:1.7rem;border-radius:8px;background:#f1f5f9;color:#64748b;font-size:.62rem}.cert-external-visibility__choices strong,.cert-external-visibility__choices small{display:block}.cert-external-visibility__choices strong{color:#334155;font-size:.58rem}.cert-external-visibility__choices small{color:#94a3b8;font-size:.48rem}.cert-external-visibility__choices>label.is-active{border-color:#8b5cf6;background:#f5f3ff}.cert-external-visibility__choices>label.is-active>i{background:#7c3aed;color:#fff}.cert-external-visible-at{margin-top:.15rem}
    .cert-external-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.65rem}
    .cert-external-upload{position:relative;display:grid;place-items:center;gap:.2rem;padding:1.2rem;border:2px dashed #c4b5fd;border-radius:13px;background:#faf5ff;color:#7c3aed;text-align:center;cursor:pointer;transition:border-color .15s,background .15s}
    .cert-external-upload:hover{border-color:#7c3aed;background:#f5f3ff}.cert-external-upload input{position:absolute;inset:0;width:100%;height:100%;opacity:0;cursor:pointer}
    .cert-external-upload>i{font-size:1.2rem}.cert-external-upload strong{font-size:.67rem}.cert-external-upload small{color:#8b5cf6;font-size:.53rem}.cert-external-upload em{color:#7c3aed;font-size:.54rem;font-style:normal;font-weight:800}
    .cert-external-modal__footer{display:flex;align-items:center;justify-content:space-between;gap:.75rem;padding:.85rem 1.25rem;border-top:1px solid #e2e8f0;background:#fff}
    .cert-external-modal__footer>span{color:#64748b;font-size:.55rem}.cert-external-modal__footer>span i{color:#16a34a}.cert-external-modal__footer>div{display:flex;align-items:center;gap:.5rem}
    @media(max-width:700px){.cert-external-students,.cert-external-grid,.cert-external-visibility__choices{grid-template-columns:1fr}.cert-external-modal__footer{align-items:stretch;flex-direction:column}.cert-external-modal__footer>div{justify-content:flex-end}}
    @media(max-width:1100px){
        .cert-admin-stats{grid-template-columns:repeat(2,1fr)}
        .cert-policy-grid{grid-template-columns:repeat(2,1fr)}
        .cert-col-code{width:14%}
        .cert-col-holder{width:16%}
        .cert-col-program{width:18%}
        .cert-col-issued{width:11%}
        .cert-col-status{width:12%}
        .cert-col-actions{width:29%}
    }
    @media(max-width:900px){.cert-admin-hero{align-items:flex-start;flex-direction:column}.cert-admin-stats{grid-template-columns:1fr}.cert-policy-grid{grid-template-columns:1fr}.cert-auto-flow{align-items:stretch;flex-direction:column}.cert-auto-flow>i{transform:rotate(-90deg);align-self:center}.cert-policy-panel__footer{align-items:stretch;flex-direction:column}.cert-issue-layout{grid-template-columns:1fr!important}.cert-issue-fields{grid-template-columns:1fr}.cert-issue-footer{flex-direction:column;align-items:stretch}.cert-issue-submit{width:100%}.cert-issue-stepper{flex-wrap:wrap}.cert-issue-access__grid{grid-template-columns:1fr}.cert-filter-panel{margin-inline:.6rem}.cert-filter-panel__main{flex-direction:column;align-items:stretch}.cert-filter-search{min-width:0}.cert-filter-panel__row{flex-direction:column;align-items:stretch}.cert-filter-field,.cert-filter-field--sort{min-width:0;flex:1}.cert-clear-filters{width:100%}.cert-results-toolbar{margin-inline:.6rem;flex-direction:column;align-items:stretch}.cert-table-wrap{margin-inline:.6rem;overflow-x:auto}.cert-table{min-width:720px}.cert-action span{display:none}.cert-details-modal__grid{grid-template-columns:1fr}}
</style>
@endpush

@include('partials.admin.shell-end')
