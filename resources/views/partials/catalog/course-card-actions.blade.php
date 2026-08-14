@php
    $showUrl = $showUrl ?? '#';
    $enrollUrl = $enrollUrl ?? $showUrl;
    $courseId = $courseId ?? null;
    $compact = $compact ?? false;
    $showWish = $showWish ?? true;
@endphp

<div @class(['course-card-actions', 'course-card-actions--compact' => $compact])>
    <a href="{{ $enrollUrl }}" class="btn btn-primary course-card-actions__enroll">
        التسجيل والدفع
    </a>
    <a href="{{ $showUrl }}" class="btn btn-outline-primary course-card-actions__details">
        عرض التفاصيل
    </a>
    @if ($showWish)
        @include('partials.catalog.course-card-wish', ['courseId' => $courseId])
    @endif
</div>
