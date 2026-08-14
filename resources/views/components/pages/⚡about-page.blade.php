<?php

use App\Services\CmsPageService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-inner')]
#[Title('منصة مركز التعلم المستمر – تدريب احترافي وتنمية مهارات')]
class extends Component
{
    #[Computed]
    public function cmsPage()
    {
        return app(CmsPageService::class)->findPublishedByType('about');
    }
};
?>

<div>
    @include('partials.cms.page-render', [
        'page' => $this->cmsPage,
        'pageType' => 'about',
        'locale' => app()->getLocale(),
    ])
</div>
