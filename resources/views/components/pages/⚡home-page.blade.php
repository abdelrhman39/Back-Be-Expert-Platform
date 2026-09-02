<?php

use App\Services\ArticleService;
use App\Services\HomePageService;
use App\Services\CmsPageService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')]
class extends Component
{
    #[Computed]
    public function cmsPage()
    {
        return app(CmsPageService::class)->findPublishedByType('home');
    }

    #[Computed]
    public function popularFields()
    {
        return app(HomePageService::class)->popularFields();
    }

    #[Computed]
    public function professionalCertificates()
    {
        return app(HomePageService::class)->professionalCertificates();
    }

    #[Computed]
    public function diplomas()
    {
        return app(HomePageService::class)->diplomas();
    }

    #[Computed]
    public function latestArticles()
    {
        return app(ArticleService::class)->latestPublished(6);
    }
};
?>

<div>
    @include('partials.cms.page-render', [
        'page' => $this->cmsPage,
        'pageType' => 'home',
        'locale' => app()->getLocale(),
        'forceShowTitle' => false,
        'context' => [
            'popularFields' => $this->popularFields,
            'professionalCertificates' => $this->professionalCertificates,
            'diplomas' => $this->diplomas,
            'latestArticles' => $this->latestArticles,
            'heroMetrics' => app(HomePageService::class)->heroMetrics(),
        ],
    ])
</div>
