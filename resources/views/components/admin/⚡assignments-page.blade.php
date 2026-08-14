<?php

use App\Models\Assignment;
use App\Support\AssignmentOptions;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('الواجبات | لوحة التحكم')]
class extends Component
{
    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $sectionId = '';

    #[Computed]
    public function assignments()
    {
        return Assignment::query()
            ->with(['section', 'session'])
            ->withCount('submissions')
            ->when($this->sectionId, fn ($q) => $q->where('section_id', (int) $this->sectionId))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('title', 'like', '%'.$this->search.'%')
                    ->orWhereHas('section', fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'));
            }))
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'published' => Assignment::query()->where('status', 'published')->count(),
            'draft' => Assignment::query()->where('status', 'draft')->count(),
            'total' => Assignment::query()->count(),
        ];
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.assignments'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['label' => 'الواجبات'],
    ],
])

<section class="admin-crud-card admin-crud-card--filter">
    <div class="admin-crud-card__head admin-crud-card__head--row">
        <h2>الواجبات الدراسية
            <span class="admin-crud-card__meta">— {{ $this->stats['published'] }} منشورة</span>
        </h2>
        <a href="{{ route('admin.assignments.create') }}" class="admin-btn-primary admin-btn-primary--sm">+ واجب جديد</a>
    </div>
    <div class="admin-filter-grid">
        <div class="admin-field">
            <label>بحث</label>
            <input type="search" class="admin-control" wire:model.live.debounce.300ms="search" placeholder="عنوان، شعبة...">
        </div>
        <div class="admin-field">
            <label>الحالة</label>
            <select class="admin-control" wire:model.live="status">
                <option value="">الكل</option>
                @foreach (AssignmentOptions::statuses() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="admin-field">
            <label>معرّف الشعبة</label>
            <input type="number" class="admin-control" wire:model.live="sectionId" placeholder="section_id">
        </div>
    </div>
</section>

<section class="admin-crud-card">
    <div class="admin-table-wrap">
        <table class="admin-data-table">
            <thead>
                <tr>
                    <th>العنوان</th>
                    <th>الشعبة</th>
                    <th>النطاق</th>
                    <th>الموعد النهائي</th>
                    <th>الحالة</th>
                    <th>تسليمات</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->assignments as $assignment)
                    <tr wire:key="assignment-{{ $assignment->id }}">
                        <td><strong>{{ $assignment->title }}</strong></td>
                        <td>
                            @if ($assignment->section)
                                <a href="{{ route('admin.sections.show', $assignment->section) }}" class="dash-inline-link">{{ $assignment->section->name }}</a>
                            @else — @endif
                        </td>
                        <td>{{ AssignmentOptions::scopeLabel($assignment->scope) }}</td>
                        <td dir="ltr">{{ $assignment->due_at?->format('Y-m-d H:i') ?? '—' }}</td>
                        <td>
                            <span @class([
                                'admin-badge',
                                'admin-badge--success' => $assignment->status === 'published',
                                'admin-badge--warn' => $assignment->status === 'draft',
                                'admin-badge--muted' => in_array($assignment->status, ['closed', 'archived'], true),
                            ])>{{ AssignmentOptions::statusLabel($assignment->status) }}</span>
                        </td>
                        <td>{{ $assignment->submissions_count }}</td>
                        <td>
                            <a href="{{ route('admin.assignments.show', $assignment) }}" class="admin-btn-secondary admin-btn-secondary--sm">عرض</a>
                            <a href="{{ route('admin.assignments.edit', $assignment) }}" class="admin-btn-secondary admin-btn-secondary--sm">تعديل</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="text-align:center;padding:1.5rem">لا توجد واجبات.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

@include('partials.admin.shell-end')

@push('styles')
<style>
    .admin-badge--muted { background: #f1f5f9; color: #64748b; }
    .admin-crud-card__head--row { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem; }
</style>
@endpush
