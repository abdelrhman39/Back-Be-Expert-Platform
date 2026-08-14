<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-inner')]
#[Title('الأسئلة الشائعة | مركز التعلم المستمر')]
class extends Component
{
    //
};
?>

<div class="support-page">
    @include('partials.support.nav', ['active' => 'faq'])

    <div class="container support-page__container support-page__container--narrow">
        <h1 class="support-page__title text-center">الأسئلة الشائعة</h1>
        <p class="support-page__lead text-center mb-4 mb-lg-5">قمنا بتجميع أكثر الأسئلة شيوعاً لمساعدتك في العثور على الإجابات التي تحتاجها بسرعة وسهولة.</p>

        @include('partials.support.faq-items')
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('css/support-pages.css') }}">
@endpush
