<?php

use App\Models\AcademicProgram;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('برنامج دراسي | لوحة التحكم')]
class extends Component
{
    public ?int $programId = null;

    public string $nameAr = '';

    public string $nameEn = '';

    public string $nameOnCertificate = '';

    public string $code = '';

    public string $symbol = '';

    public string $type = 'diploma';

    public string $status = 'active';

    public string $studyStatus = '';

    public ?int $durationMonths = null;

    public string $durationLabel = '';

    public ?string $startDate = null;

    public string $coordinator = '';

    public string $city = '';

    public string $email = '';

    public string $phone = '';

    public string $mediaUrl = '';

    public string $posterImage = '';

    public string $summary = '';

    public string $skillsText = '';

    /** @var array<int, array{name: string, url: string}> */
    public array $attachmentRows = [];

    public function mount(?AcademicProgram $program = null): void
    {
        if (! $program) {
            return;
        }

        $this->programId = $program->id;
        $this->nameAr = $program->name_ar;
        $this->nameEn = $program->name_en ?? '';
        $this->nameOnCertificate = $program->name_on_certificate ?? '';
        $this->code = $program->code;
        $this->symbol = $program->symbol ?? '';
        $this->type = $program->type;
        $this->status = $program->status;
        $this->studyStatus = $program->study_status ?? '';
        $this->durationMonths = $program->duration_months;
        $this->durationLabel = $program->duration_label ?? '';
        $this->startDate = $program->start_date?->format('Y-m-d');
        $this->coordinator = $program->coordinator ?? '';
        $this->city = $program->city ?? '';
        $this->email = $program->email ?? '';
        $this->phone = $program->phone ?? '';
        $this->mediaUrl = $program->media_url ?? '';
        $this->posterImage = $program->poster_image ?? '';
        $this->summary = $program->summary ?? '';
        $this->skillsText = implode("\n", $program->skills ?? []);
        $this->attachmentRows = $program->attachments ?? [];
    }

    public function addAttachment(): void
    {
        $this->attachmentRows[] = ['name' => '', 'url' => ''];
    }

    public function removeAttachment(int $index): void
    {
        unset($this->attachmentRows[$index]);
        $this->attachmentRows = array_values($this->attachmentRows);
    }

    public function save(): void
    {
        $validated = $this->validate($this->rules(), [], $this->attributeNames());

        $skills = collect(preg_split('/\r\n|\r|\n/', $this->skillsText))
            ->map(fn ($s) => trim($s))
            ->filter()
            ->values()
            ->all();

        $attachments = collect($this->attachmentRows)
            ->filter(fn ($row) => trim($row['name'] ?? '') !== '')
            ->map(fn ($row) => [
                'name' => trim($row['name']),
                'url' => trim($row['url'] ?? '') ?: null,
            ])
            ->values()
            ->all();

        $data = [
            'name_ar' => $validated['nameAr'],
            'name_en' => $validated['nameEn'] ?: null,
            'name_on_certificate' => $validated['nameOnCertificate'] ?: null,
            'code' => strtoupper($validated['code']),
            'symbol' => $validated['symbol'] ?: null,
            'type' => $validated['type'],
            'status' => $validated['status'],
            'study_status' => $validated['studyStatus'] ?: null,
            'duration_months' => $validated['durationMonths'] ?: null,
            'duration_label' => $validated['durationLabel'] ?: null,
            'start_date' => $validated['startDate'] ?: null,
            'coordinator' => $validated['coordinator'] ?: null,
            'city' => $validated['city'] ?: null,
            'email' => $validated['email'] ?: null,
            'phone' => $validated['phone'] ?: null,
            'media_url' => $validated['mediaUrl'] ?: null,
            'poster_image' => $validated['posterImage'] ?: null,
            'summary' => $validated['summary'] ?: null,
            'skills' => $skills ?: null,
            'attachments' => $attachments ?: null,
        ];

        if ($this->programId) {
            $program = AcademicProgram::query()->findOrFail($this->programId);
            $program->update($data);
            session()->flash('admin_message', 'تم تحديث البرنامج بنجاح.');
            $this->redirect(route('admin.programs.show', $program));
        } else {
            $program = AcademicProgram::query()->create($data);
            session()->flash('admin_message', 'تم إنشاء البرنامج بنجاح.');
            $this->redirect(route('admin.programs.show', $program));
        }
    }

    /** @return array<string, mixed> */
    protected function rules(): array
    {
        return [
            'nameAr' => ['required', 'string', 'max:255'],
            'nameEn' => ['nullable', 'string', 'max:255'],
            'nameOnCertificate' => ['nullable', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:32',
                Rule::unique('academic_programs', 'code')->ignore($this->programId),
            ],
            'symbol' => ['nullable', 'string', 'max:64'],
            'type' => ['required', Rule::in(array_keys(\App\Support\AcademicProgramOptions::types()))],
            'status' => ['required', Rule::in(array_keys(\App\Support\AcademicProgramOptions::statuses()))],
            'studyStatus' => ['nullable', 'string', 'max:255'],
            'durationMonths' => ['nullable', 'integer', 'min:1', 'max:60'],
            'durationLabel' => ['nullable', 'string', 'max:120'],
            'startDate' => ['nullable', 'date'],
            'coordinator' => ['nullable', 'string', 'max:120'],
            'city' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'mediaUrl' => ['nullable', 'url', 'max:500'],
            'posterImage' => ['nullable', 'string', 'max:500'],
            'summary' => ['nullable', 'string', 'max:5000'],
            'skillsText' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /** @return array<string, string> */
    protected function attributeNames(): array
    {
        return [
            'nameAr' => 'اسم البرنامج',
            'code' => 'رمز البرنامج',
            'type' => 'نوع البرنامج',
            'status' => 'الحالة',
            'email' => 'البريد الإلكتروني',
            'mediaUrl' => 'رابط صفحة البرنامج',
            'posterImage' => 'صورة البوستر',
        ];
    }
};
?>

@php
    $isEdit = (bool) $programId;
    $pageTitle = $isEdit ? 'تعديل البرنامج' : 'برنامج جديد';
@endphp

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.programs'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => route('admin.programs'), 'label' => 'البرامج الدراسية'],
        ['label' => $pageTitle],
    ],
])

<section class="admin-crud-card">
    <div class="admin-crud-card__head admin-crud-card__head--row">
        <h2>{{ $pageTitle }}</h2>
        <a href="{{ route('admin.programs') }}" class="admin-btn-secondary admin-btn-secondary--sm">العودة للقائمة</a>
    </div>

    <form wire:submit="save">
        @include('partials.admin.program-form-fields')

        <div class="admin-filter-actions" style="margin-top: 1.5rem;">
            <button type="submit" class="admin-btn-primary admin-btn-primary--sm" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">{{ $isEdit ? 'حفظ التعديلات' : 'إنشاء البرنامج' }}</span>
                <span wire:loading wire:target="save">جاري الحفظ...</span>
            </button>
            @if ($isEdit)
                <a href="{{ route('admin.programs.show', $programId) }}" class="admin-btn-secondary admin-btn-secondary--sm">إلغاء</a>
            @endif
        </div>
    </form>
</section>

@include('partials.admin.shell-end')
