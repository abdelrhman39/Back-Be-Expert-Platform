<?php

use App\Services\CmsPageService;
use App\Support\CmsBlockDefaults;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-inner')]
#[Title('تواصل معنا | مركز التعلم المستمر')]
class extends Component
{
    #[Computed]
    public function cmsPage()
    {
        return app(CmsPageService::class)->findPublishedByType('contact');
    }
};
?>

@php
    $cmsPage = $this->cmsPage;
    $cmsTranslation = $cmsPage?->translate();
    $mode = $cmsPage?->content_mode ?? CmsBlockDefaults::defaultContentMode('contact');
    $useBlocks = $cmsPage
        && $mode === 'blocks'
        && CmsBlockDefaults::hasConfiguredBlocks($cmsTranslation?->blocks);
    $normalizedBlocks = $useBlocks
        ? CmsBlockDefaults::normalize($cmsTranslation->blocks, 'contact', app()->getLocale())
        : [];
    $hasContactForm = collect($normalizedBlocks)->contains(
        fn ($block) => ($block['type'] ?? '') === 'contact_map_form' && ($block['enabled'] ?? true)
    );
@endphp

<div class="atelier-contact">
    @include('partials.cms.page-render', [
        'page' => $cmsPage,
        'pageType' => 'contact',
        'locale' => app()->getLocale(),
    ])
</div>

@if ($useBlocks || filled($cmsTranslation?->body))
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/contact-page.css') }}?v=9">
    @endpush
@endif

@if ($hasContactForm)
    @push('scripts')
        @include('partials.cms.contact-form-script')
    @endpush
@endif
