<?php

use App\Models\AcademicLevel;
use App\Models\AcademicProgram;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('مستوى أكاديمي | لوحة التحكم')]
class extends Component
{
    public ?int $levelId = null;

    public string $nameAr = '';

    public ?int $programId = null;

    public int $sortOrder = 1;

    public string $status = 'active';

    public function mount(?AcademicLevel $level = null, ?int $program = null): void
    {
        if ($level) {
            $this->levelId = $level->id;
            $this->nameAr = $level->name_ar;
            $this->programId = $level->program_id;
            $this->sortOrder = $level->sort_order;
            $this->status = $level->status;

            return;
        }

        if ($program) {
            $this->programId = $program;
        }
    }

    #[Computed]
    public function programs()
    {
        return AcademicProgram::query()->orderBy('name_ar')->get(['id', 'name_ar', 'code']);
    }

    public function save(): void
    {
        $validated = $this->validate([
            'nameAr' => ['required', 'string', 'max:255'],
            'programId' => ['required', 'exists:academic_programs,id'],
            'sortOrder' => ['required', 'integer', 'min:1', 'max:99'],
            'status' => ['required', 'in:active,inactive'],
        ], [], [
            'nameAr' => 'اسم المستوى',
            'programId' => 'البرنامج',
        ]);

        $data = [
            'name_ar' => $validated['nameAr'],
            'program_id' => $validated['programId'],
            'sort_order' => $validated['sortOrder'],
            'status' => $validated['status'],
        ];

        if ($this->levelId) {
            AcademicLevel::query()->findOrFail($this->levelId)->update($data);
            session()->flash('admin_message', 'تم تحديث المستوى.');
        } else {
            AcademicLevel::query()->create($data);
            session()->flash('admin_message', 'تم إنشاء المستوى.');
        }

        $this->redirect(route('admin.levels'));
    }
};
?>

@php $pageTitle = $levelId ? 'تعديل المستوى' : 'مستوى جديد'; @endphp

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.levels'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['href' => route('admin.levels'), 'label' => 'المستويات الأكاديمية'],
        ['label' => $pageTitle],
    ],
])

<section class="admin-crud-card">
    <div class="admin-crud-card__head admin-crud-card__head--row">
        <h2>{{ $pageTitle }}</h2>
        <a href="{{ route('admin.levels') }}" class="admin-btn-secondary admin-btn-secondary--sm">العودة</a>
    </div>
    <form wire:submit="save">
        <div class="admin-filter-grid" style="grid-template-columns:repeat(2,1fr);">
            <div class="admin-field">
                <label>اسم المستوى *</label>
                <input type="text" class="admin-control" wire:model="nameAr">
                @error('nameAr')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
            </div>
            <div class="admin-field">
                <label>البرنامج *</label>
                <select class="admin-control" wire:model="programId">
                    <option value="">—</option>
                    @foreach ($this->programs as $prog)
                        <option value="{{ $prog->id }}">{{ $prog->name_ar }}</option>
                    @endforeach
                </select>
                @error('programId')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
            </div>
            <div class="admin-field">
                <label>الترتيب</label>
                <input type="number" min="1" class="admin-control" wire:model="sortOrder">
            </div>
            <div class="admin-field">
                <label>الحالة</label>
                <select class="admin-control" wire:model="status">
                    <option value="active">مفعل</option>
                    <option value="inactive">غير مفعل</option>
                </select>
            </div>
        </div>
        <div class="admin-filter-actions" style="margin-top:1.5rem;">
            <button type="submit" class="admin-btn-primary admin-btn-primary--sm">حفظ</button>
        </div>
    </form>
</section>

@include('partials.admin.shell-end')
