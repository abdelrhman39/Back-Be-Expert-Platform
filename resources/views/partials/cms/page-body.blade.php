@php
    $layout = $layout ?? 'default';
    $showTitle = $showTitle ?? true;
    $excerpt = $excerpt ?? ($translation->excerpt ?? null);
    $previewMode = $previewMode ?? false;
@endphp

@if ($translation && ($translation->body || $translation->title))
    <div @class(['cms-page-wrap', 'cms-page-wrap--'.$layout, 'is-preview' => $previewMode])>
        @if ($previewMode && isset($page) && $page->status === 'draft')
            <div class="cms-page-draft-notice" role="status">
                <strong>مسودة</strong> — هذه الصفحة غير منشورة ولن يراها الزوار حتى تنشرها.
            </div>
        @endif

        @if ($translation->title && $showTitle)
            @include('partials.cms.page-breadcrumb', ['translation' => $translation])
        @endif

        <div @class(['page-content', 'pages-detail', 'cms-page-content-area'])>
            <div class="container">
                @if ($excerpt && ! $showTitle)
                    <p class="cms-page-lead">{{ $excerpt }}</p>
                @elseif ($excerpt && $showTitle)
                    <p class="cms-page-lead cms-page-lead--under-breadcrumb">{{ $excerpt }}</p>
                @endif

                @if ($translation->body)
                    <div class="cms-page-content">
                        {!! $translation->body !!}
                    </div>
                @elseif ($previewMode)
                    <div class="cms-page-empty">
                        <p>لا يوجد محتوى نصي بعد. افتح <strong>تعديل</strong> وأضف المحتوى من تبويب «عربي».</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif
