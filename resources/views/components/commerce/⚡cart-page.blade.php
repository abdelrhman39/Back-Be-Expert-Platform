<?php

use App\Services\CartService;
use App\Services\GuestAccountService;
use App\Services\WishlistService;
use App\Support\PublicCopy;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

new class extends Component
{
    public $items = [];

    public int $count = 0;

    public float $total = 0;

    public string $guestName = '';

    public string $guestEmail = '';

    public string $guestPhone = '';

    public function layout(): string
    {
        return auth()->check() ? 'layouts.app-user' : 'layouts.app-inner';
    }

    public function rendering($view): void
    {
        $title = PublicCopy::cart('title');
        $view->title($title.' | '.platform_name());
        $view->layoutData(['metaDescription' => PublicCopy::cart(auth()->check() ? 'intro' : 'intro_empty')]);
    }

    public function mount(CartService $cart): void
    {
        $this->refreshCart($cart);
    }

    public function removeItem(int $itemId, CartService $cart, WishlistService $wishlist): void
    {
        $cart->removeItem($itemId);
        $this->refreshCart($cart);
        $this->dispatch('commerce-counts-updated', cartCount: $this->count, wishlistCount: $wishlist->count());
    }

    public function registerAndCheckout(GuestAccountService $accounts, CartService $cart, WishlistService $wishlist): void
    {
        $locale = app()->getLocale();

        if (auth()->check()) {
            $this->redirect(route('checkout', ['locale' => $locale]), navigate: true);

            return;
        }

        if ($this->count < 1) {
            $this->addError('guestEmail', PublicCopy::cart('empty_before', $locale));

            return;
        }

        $validated = $this->validate([
            'guestName' => ['required', 'string', 'max:255'],
            'guestEmail' => ['required', 'email', 'max:255'],
            'guestPhone' => ['required', 'string', 'min:9', 'max:20'],
        ], [], [
            'guestName' => PublicCopy::cart('name', $locale),
            'guestEmail' => PublicCopy::cart('email', $locale),
            'guestPhone' => PublicCopy::cart('phone', $locale),
        ]);

        try {
            $result = $accounts->registerAndLogin([
                'name' => $validated['guestName'],
                'email' => $validated['guestEmail'],
                'phone' => $validated['guestPhone'],
            ]);
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $field => $messages) {
                $mapped = match ($field) {
                    'email' => 'guestEmail',
                    'phone' => 'guestPhone',
                    'name' => 'guestName',
                    default => $field,
                };
                foreach ($messages as $message) {
                    $this->addError($mapped, $message);
                }
            }

            return;
        }

        $this->refreshCart($cart);
        $this->dispatch('commerce-counts-updated', cartCount: $this->count, wishlistCount: $wishlist->count());

        session()->flash(
            'portal_message',
            PublicCopy::cart($result['created'] ? 'flash_created' : 'flash_existing', $locale)
        );

        $this->redirect(route('checkout', ['locale' => $locale]), navigate: true);
    }

    protected function refreshCart(CartService $cart): void
    {
        $this->items = $cart->items();
        $this->count = $this->items->count();
        $this->total = $cart->total();
    }
};
?>

@php
    $locale = app()->getLocale();
    $t = fn (string $key) => \App\Support\PublicCopy::cart($key, $locale);
    $sar = $t('sar');
@endphp

@include('partials.commerce-styles')

@push('styles')
    @unless (auth()->check())
        <link rel="stylesheet" href="{{ asset('css/apply-form.css') }}?v=3">
    @endunless
    <link rel="stylesheet" href="{{ asset('css/cart.css') }}?v=3">
@endpush

@if (auth()->check())
    @include('partials.commerce.cart-portal')
@else
    @include('partials.commerce.cart-guest')
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
