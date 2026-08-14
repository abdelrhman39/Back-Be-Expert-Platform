<?php

use App\Models\CatalogCourse;
use App\Services\CatalogCourseService;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app-inner')]
class extends Component
{
    public CatalogCourse $course;

    public function mount(string $locale, CatalogCourse $course): void
    {
        abort_unless($course->status === 'published', 404);

        $this->course = $course->load(['details', 'modules.lessons', 'categories', 'academicProgram.batches']);
    }

    #[Computed]
    public function relatedCourses()
    {
        return app(CatalogCourseService::class)->related($this->course, 12);
    }

    public function with(): array
    {
        $brief = $this->course->details?->tabContent('brief');

        return [
            'metaDescription' => $brief
                ? Str::limit(strip_tags($brief), 160)
                : $this->course->displayTitle(),
            'title' => $this->course->displayTitle().' | منصة مركز التعلم المستمر',
        ];
    }
};
?>

@php
    $pageTitle = $course->displayTitle().' | منصة مركز التعلم المستمر';
@endphp

<div>
    @include('partials.catalog.course-show-breadcrumb', ['course' => $course])

    <div class="page-content content position-relative fellowship">
        <div class="container">
            <div class="row">
                @include('partials.catalog.course-show-main', ['course' => $course])
                @include('partials.catalog.course-show-sidebar', [
                    'course' => $course,
                ])
            </div>

            @include('partials.catalog.course-show-related', [
                'relatedCourses' => $this->relatedCourses,
            ])
        </div>
    </div>
</div>

@push('scripts')
    <script>document.title = @json($pageTitle);</script>
    <script src="{{ asset('js/course-enroll-sheet.js') }}?v=1" defer></script>
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/course-enroll.css') }}?v=4">
    <link rel="stylesheet" href="{{ asset('css/course-show.css') }}?v=3">
@endpush
