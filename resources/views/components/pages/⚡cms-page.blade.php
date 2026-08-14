<?php

use App\Services\CmsPageService;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app-inner')]
class extends Component
{
    public string $slug;

    public ?\App\Models\CmsPage $cmsPage = null;

    public function mount(string $slug, CmsPageService $pages): void
    {
        $this->slug = $slug;
        $this->cmsPage = $pages->findPublishedBySlug($slug);

        if (! $this->cmsPage) {
            abort(404);
        }
    }

    public function title(): string
    {
        $t = $this->cmsPage?->translate();

        return ($t?->meta_title ?: $t?->title ?: 'صفحة').' | مركز التعلم المستمر';
    }
};
?>

@php
    $translation = $cmsPage?->translate();
@endphp

@if ($translation?->meta_description)
    @push('head')
        <meta name="description" content="{{ $translation->meta_description }}">
    @endpush
@endif

@if ($cmsPage->noindex ?? false)
    @push('head')
        <meta name="robots" content="noindex, nofollow">
    @endpush
@endif

@if ($translation?->og_image)
    @push('head')
        <meta property="og:image" content="{{ $translation->og_image }}">
    @endpush
@endif

@include('partials.cms.page-render', [
    'page' => $cmsPage,
    'pageType' => $cmsPage->type,
    'locale' => app()->getLocale(),
])

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/cms-public.css') }}">
@endpush
