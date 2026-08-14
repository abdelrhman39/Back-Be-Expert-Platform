<?php

use App\Models\CatalogCourse;
use App\Models\CatalogCourseLesson;
use App\Services\CatalogCourseService;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app-inner')]
class extends Component
{
    public CatalogCourse $course;

    public CatalogCourseLesson $lesson;

    public function mount(string $locale, CatalogCourse $course, CatalogCourseLesson $lesson, CatalogCourseService $catalog): void
    {
        abort_unless($catalog->findPreviewLesson($course, $lesson->id), 404);

        $this->course = $course;
        $this->lesson = $lesson->load('module');
    }
};
?>

@php($locale = app()->getLocale())

<div class="catalog-show catalog-preview">
    <div class="breadcrumb-bar">
        <div class="container">
            <nav aria-label="breadcrumb" class="page-breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home', ['locale' => $locale]) }}">الرئيسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('courses.index', ['locale' => $locale]) }}">الدورات</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('courses.show', ['locale' => $locale, 'course' => $course->showSlug()]) }}">{{ Str::limit($course->displayTitle(), 32) }}</a></li>
                    <li class="breadcrumb-item active">معاينة مجانية</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="container catalog-preview__body">
        <div class="catalog-preview__banner">
            <span class="catalog-preview__badge">معاينة مجانية</span>
            <h1>{{ $lesson->displayTitle() }}</h1>
            <p class="catalog-preview__course">{{ $course->displayTitle() }}</p>
        </div>

        <div class="catalog-preview__stage">
            @if ($lesson->type === 'video' && $lesson->videoEmbedUrl())
                <div class="catalog-preview__video">
                    <iframe src="{{ $lesson->videoEmbedUrl() }}" title="{{ $lesson->displayTitle() }}" allowfullscreen loading="lazy"></iframe>
                </div>
            @endif

            @if ($lesson->displayBody())
                <article class="catalog-tab-content">
                    {!! $lesson->displayBody() !!}
                </article>
            @endif

            @if ($lesson->resource_url)
                <p class="mt-3">
                    <a href="{{ $lesson->resource_url }}" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener">مورد إضافي</a>
                </p>
            @endif
        </div>

        <div class="catalog-preview__cta">
            <p>أعجبك المحتوى؟ سجّل في الدورة للوصول لكامل المنهج.</p>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('courses.show', ['locale' => $locale, 'course' => $course->showSlug()]) }}" class="btn btn-primary">تفاصيل الدورة</a>
                <button type="button"
                    class="btn btn-outline-primary Add-to-Cart"
                    data-course_id="{{ $course->id }}"
                    data-single-type="{{ $course->delivery_type }}"
                    data-price="{{ $course->displayPriceValue() ?? 0 }}">
                    أضف للسلة
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/catalog-public.css') }}">
@endpush

@push('scripts')
    <script>document.title = @json('معاينة: '.$lesson->displayTitle().' | '.$course->displayTitle());</script>
@endpush
