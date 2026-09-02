<?php

use App\Services\CmsPageService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-inner')]
#[Title('عن المنصة | مركز التعلم المستمر')]
class extends Component
{
    #[Computed]
    public function cmsPage()
    {
        return app(CmsPageService::class)->findPublishedByType('about');
    }
};
?>

<div class="atelier-about">
    @include('partials.cms.page-render', [
        'page' => $this->cmsPage,
        'pageType' => 'about',
        'locale' => app()->getLocale(),
    ])
</div>

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/home-mvg.css') }}?v=3">
    <link rel="stylesheet" href="{{ asset('css/home-identity-blocks.css') }}?v=4">
    <link rel="stylesheet" href="{{ asset('css/about-page.css') }}?v=3">
@endpush
