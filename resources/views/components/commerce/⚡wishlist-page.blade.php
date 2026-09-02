<?php

use App\Services\CartService;
use App\Services\WishlistService;
use App\Support\PublicCopy;
use Livewire\Component;

new class extends Component
{
    public $items = [];

    public int $count = 0;

    public function layout(): string
    {
        return auth()->check() ? 'layouts.app-user' : 'layouts.app-inner';
    }

    public function rendering($view): void
    {
        $title = PublicCopy::wishlist('title');
        $view->title($title.' | '.platform_name());
        $view->layoutData(['metaDescription' => PublicCopy::wishlist($this->count > 0 ? 'intro' : 'intro_empty')]);
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

@php
    $locale = app()->getLocale();
    $t = fn (string $key) => \App\Support\PublicCopy::wishlist($key, $locale);
@endphp

@include('partials.commerce-styles')

@push('styles')
    @unless (auth()->check())
        <link rel="stylesheet" href="{{ asset('css/apply-form.css') }}?v=3">
    @endunless
    <link rel="stylesheet" href="{{ asset('css/cart.css') }}?v=4">
@endpush

@if (auth()->check())
    @include('partials.commerce.wishlist-portal')
@else
    @include('partials.commerce.wishlist-guest')
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
