<?php

use App\Services\CartService;
use App\Services\WishlistService;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('قائمة المفضلة | منصة مركز التعلم المستمر')]
class extends Component
{
    public $items = [];

    public int $count = 0;

    public function layout(): string
    {
        return auth()->check() ? 'layouts.app-user' : 'layouts.app-inner';
    }

    public function mount(WishlistService $wishlist): void
    {
        $this->refreshWishlist($wishlist);
    }

    public function removeItem(int $itemId, WishlistService $wishlist, CartService $cart): void
    {
        $wishlist->removeItem($itemId);
        $this->refreshWishlist($wishlist);
        $this->dispatch('commerce-counts-updated', wishlistCount: $this->count, cartCount: $cart->count());
    }

    protected function refreshWishlist(WishlistService $wishlist): void
    {
        $this->items = $wishlist->items();
        $this->count = $this->items->count();
    }
};
?>

@php($locale = app()->getLocale())

@if (auth()->check())
    @include('partials.portal.shell-start', ['portalActive' => 'profile', 'portalTitle' => 'قائمة المفضلة'])
    <div class="portal-dashboard portal-commerce-page">
        @include('partials.commerce-styles')
        <div class="portal-commerce-intro">
            <div>
                <h1 class="portal-commerce-intro__title">قائمة المفضلة</h1>
                <p class="portal-commerce-intro__desc">{{ $count }} دورة محفوظة للمراجعة لاحقاً</p>
            </div>
        </div>
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="portal-panel p-3">
                    <div class="commerce-list">
                        @forelse ($items as $item)
                            @include('components.commerce.wishlist-item', ['item' => $item])
                        @empty
                            <div class="portal-commerce-empty">
                                <p class="text-muted mb-3">لا توجد دورات في المفضلة</p>
                                <a href="{{ route('courses.index', ['locale' => $locale]) }}" class="btn btn-primary btn-sm">اكتشف الدورات</a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
            @if ($count > 0)
                <div class="col-lg-4">
                    <div class="portal-panel p-3">
                        <h2 class="portal-panel__title mb-3"><i class="fa-solid fa-heart"></i> ملخص المفضلة</h2>
                        <p class="mb-3"><strong>{{ $count }}</strong> دورة محفوظة</p>
                        <a href="{{ route('cart', ['locale' => $locale]) }}" class="btn btn-outline-primary w-100">عرض السلة</a>
                    </div>
                </div>
            @endif
        </div>
    </div>
    @include('partials.portal.shell-end')
@else
    @include('partials.commerce-styles')
    <div class="commerce-page">
        <div class="page-content content py-0">
            <div class="container">
                <div class="dashboard-header d-flex flex-wrap align-items-end justify-content-between gap-2">
                    <div class="main-titlee">
                        <h3 class="mb-1">قائمة المفضلة</h3>
                        <p class="text-muted mb-0">الدورات التي حفظتها للمراجعة لاحقاً</p>
                    </div>
                    <div class="head-info"><p class="mb-0">عدد الدورات <span class="text-primary fw-semibold">({{ $count }})</span></p></div>
                </div>
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="commerce-list">
                            @forelse ($items as $item)
                                @include('components.commerce.wishlist-item', ['item' => $item])
                            @empty
                                <div class="commerce-empty text-center">
                                    <img src="{{ static_asset('assets/vendor/images/site-favicon.png') }}" alt="">
                                    <p class="text-muted mt-3 mb-4">لا توجد دورات في قائمة المفضلة</p>
                                    <a href="{{ route('courses.index', ['locale' => $locale]) }}" class="btn btn-primary">اكتشف دوراتنا</a>
                                </div>
                            @endforelse
                        </div>
                    </div>
                    @if ($count > 0)
                        <div class="col-lg-4">
                            <div class="commerce-summary-card p-4 sticky-top" style="top:100px">
                                <h5 class="mb-3 fw-bold">ملخص المفضلة</h5>
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <span>عدد الدورات المحفوظة</span>
                                    <span class="summary-total">{{ $count }}</span>
                                </div>
                                <a href="{{ route('cart', ['locale' => $locale]) }}" class="btn btn-outline-primary w-100 py-2">عرض السلة</a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif

@script
<script>
    $wire.on('commerce-counts-updated', (payload) => {
        if (typeof window.domainUpdateCommerceCounts === 'function') {
            window.domainUpdateCommerceCounts(payload || {});
        }
    });
</script>
@endscript
