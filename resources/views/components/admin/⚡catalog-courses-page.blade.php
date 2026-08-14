<?php

use App\Models\CatalogCourse;
use App\Services\AdminCourseContentService;
use App\Services\CatalogCourseService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin', [
    'adminLayout' => 'app',
    'adminBreadcrumb' => [['href' => '/admin', 'label' => 'الرئيسية'], ['label' => 'دورات الكتالوج']],
])]
#[Title('دورات الكتالوج | لوحة التحكم')]
class extends Component
{
    use WithPagination;

    public string $search = '';

    #[Url]
    public string $category = '';

    public function updatedSearch(): void { $this->resetPage(); }

    public function updatedCategory(): void { $this->resetPage(); }

    #[Computed]
    public function courses()
    {
        return CatalogCourse::query()
            ->with('categories')
            ->when($this->search, fn ($q) => $q->where('title_ar', 'like', '%'.$this->search.'%'))
            ->when($this->category !== '', function ($q) {
                $q->whereHas('categories', fn ($c) => $c->whereKey((int) $this->category));
            })
            ->orderByDesc('id')
            ->paginate(20);
    }

    public function contentStats(int $courseId): array
    {
        $course = CatalogCourse::query()->find($courseId);

        return $course
            ? app(AdminCourseContentService::class)->stats($course)
            : ['modules' => 0, 'lessons' => 0];
    }
};
?>

@php
    $canManage = auth()->user()?->canAdmin('catalog.manage');
    $diplomaCategory = \App\Services\CatalogCourseService::CATEGORY_DIPLOMAS;
    $certCategory = \App\Services\CatalogCourseService::CATEGORY_PROFESSIONAL_CERTIFICATES;
@endphp

@include('partials.admin.shell-start', ['shellLayout' => 'app', 'shellSidebarActive' => route('admin.catalog-courses')])

<section class="admin-crud-card admin-crud-card--filter">
    <div class="admin-crud-card__head admin-crud-card__head--row">
        <div>
            <h2>دورات الكتالوج والدبلومات</h2>
            <span class="admin-crud-card__meta">أضف شهادات احترافية أو دبلومات بنفس صفحة التفاصيل والتسجيل عبر السلة.</span>
        </div>
        @if ($canManage)
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.catalog-courses.create', ['category' => $diplomaCategory]) }}" class="admin-btn-primary">إضافة دبلوم</a>
                <a href="{{ route('admin.catalog-courses.create', ['category' => $certCategory]) }}" class="admin-btn-secondary">إضافة شهادة احترافية</a>
            </div>
        @endif
    </div>
    <div class="admin-form-grid admin-form-grid--2" style="display:grid;grid-template-columns:2fr 1fr;gap:1rem">
        <div class="admin-field">
            <input type="search" class="admin-control" wire:model.live.debounce.300ms="search" placeholder="بحث بعنوان الدورة...">
        </div>
        <div class="admin-field">
            <select class="admin-control" wire:model.live="category">
                <option value="">كل التصنيفات</option>
                <option value="{{ $diplomaCategory }}">الدبلومات</option>
                <option value="{{ $certCategory }}">الشهادات الاحترافية</option>
            </select>
        </div>
    </div>
</section>

<section class="admin-crud-card">
    <div class="admin-table-wrap">
        <table class="admin-data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>العنوان</th>
                    <th>التصنيف</th>
                    <th>السعر</th>
                    <th>المحتوى</th>
                    <th>الحالة</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->courses as $course)
                    @php
                        $content = $this->contentStats($course->id);
                        $categoryLabel = $course->categories->pluck('title_ar')->filter()->implode(' · ') ?: '—';
                    @endphp
                    <tr>
                        <td>{{ $course->id }}</td>
                        <td>
                            <strong>{{ $course->title_ar }}</strong>
                            @if ($course->academic_program_id)
                                <div class="admin-crud-card__meta">مرتبط ببرنامج أكاديمي #{{ $course->academic_program_id }}</div>
                            @endif
                        </td>
                        <td>{{ $categoryLabel }}</td>
                        <td>{{ $course->displayPrice() ?? '—' }}</td>
                        <td>
                            <span class="admin-crud-card__meta">{{ $content['modules'] }} وحدة · {{ $content['lessons'] }} درس</span>
                        </td>
                        <td>{{ $course->status === 'published' ? 'منشور' : $course->status }}</td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap justify-content-end">
                                @if ($canManage)
                                    <a href="{{ route('admin.catalog-courses.edit', ['course' => $course->id]) }}" class="admin-btn-secondary admin-btn-secondary--sm">تحرير التفاصيل</a>
                                @endif
                                <a href="{{ route('admin.catalog-courses.content', ['course' => $course->id]) }}" class="admin-btn-secondary admin-btn-secondary--sm">
                                    الوحدات
                                </a>
                                <a href="{{ route('courses.show', ['locale' => app()->getLocale(), 'course' => $course->showSlug()]) }}" class="admin-btn-secondary admin-btn-secondary--sm" target="_blank" rel="noopener">معاينة</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="text-align:center;padding:2rem">لا توجد دورات. ابدأ بإضافة دبلوم أو شهادة.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($this->courses->hasPages())
        {{ $this->courses->links() }}
    @endif
</section>

@include('partials.admin.shell-end')
