<?php

use App\Services\CartService;
use App\Services\GuestAccountService;
use App\Services\WishlistService;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('سلة التسوق | منصة مركز التعلم المستمر')]
class extends Component
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
        if (auth()->check()) {
            $this->redirect(route('checkout', ['locale' => app()->getLocale()]), navigate: true);

            return;
        }

        if ($this->count < 1) {
            $this->addError('guestEmail', 'أضف دورة واحدة على الأقل قبل التسجيل.');

            return;
        }

        $validated = $this->validate([
            'guestName' => ['required', 'string', 'max:255'],
            'guestEmail' => ['required', 'email', 'max:255'],
            'guestPhone' => ['required', 'string', 'min:9', 'max:20'],
        ], [], [
            'guestName' => 'الاسم الكامل',
            'guestEmail' => 'البريد الإلكتروني',
            'guestPhone' => 'رقم الجوال',
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
            $result['created']
                ? 'تم إنشاء حسابك وتسجيل دخولك. راجع بريدك لكلمة المرور ثم أكمل السداد.'
                : 'تم تسجيل دخولك. يمكنك متابعة إتمام الشراء.'
        );

        $this->redirect(route('checkout', ['locale' => app()->getLocale()]), navigate: true);
    }

    protected function refreshCart(CartService $cart): void
    {
        $this->items = $cart->items();
        $this->count = $this->items->count();
        $this->total = $cart->total();
    }
};
?>

@php($locale = app()->getLocale())

@include('partials.commerce-styles')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/cart.css') }}?v=2">
@endpush

@if (auth()->check())
    @include('partials.portal.shell-start', ['portalActive' => 'profile', 'portalTitle' => 'سلة التسوق'])
    <div class="portal-dashboard portal-commerce-page portal-cart-page">
        <section class="portal-commerce-hero">
            <div class="portal-commerce-hero__main">
                <span class="portal-commerce-hero__icon" aria-hidden="true">
                    <i class="fa-solid fa-cart-shopping"></i>
                </span>
                <div class="portal-commerce-hero__text">
                    <h1 class="portal-commerce-hero__title">سلة التسوق</h1>
                    <p class="portal-commerce-hero__desc">
                        راجع برامجك ثم أكمل الشراء — اضغط على اسم البرنامج لعرض تفاصيله
                    </p>
                </div>
            </div>
            <div class="portal-commerce-hero__aside">
                <div class="portal-commerce-hero__stats">
                    <div class="portal-commerce-hero__stat">
                        <span class="portal-commerce-hero__stat-label">العناصر</span>
                        <strong class="portal-commerce-hero__stat-value">{{ $count }}</strong>
                    </div>
                    @if ($count > 0)
                        <div class="portal-commerce-hero__stat portal-commerce-hero__stat--total">
                            <span class="portal-commerce-hero__stat-label">الإجمالي</span>
                            <strong class="portal-commerce-hero__stat-value" dir="ltr">{{ number_format($total, 2) }} <small>ر.س</small></strong>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        @if ($count === 0)
            <section class="portal-panel portal-commerce-empty-panel">
                <div class="portal-commerce-empty">
                    <span class="portal-commerce-empty__icon" aria-hidden="true">
                        <i class="fa-solid fa-cart-shopping"></i>
                    </span>
                    <h2 class="portal-commerce-empty__title">سلة التسوق فارغة</h2>
                    <p class="portal-commerce-empty__hint">لم تُضف أي برنامج بعد. تصفّح الدورات والدبلومات واختر ما يناسبك.</p>
                    <div class="portal-commerce-empty__actions">
                        <a href="{{ route('courses.index', ['locale' => $locale]) }}" class="btn btn-primary">
                            <i class="fa-solid fa-compass"></i> اكتشف البرامج
                        </a>
                        <a href="{{ route('learning-list', ['locale' => $locale]) }}" class="btn btn-outline-secondary">
                            قائمة التعلم
                        </a>
                    </div>
                </div>
            </section>
        @else
            <div class="row g-3 portal-commerce-layout">
                <div class="col-lg-8">
                    <section class="portal-panel">
                        <div class="portal-panel__head">
                            <h2 class="portal-panel__title"><i class="fa-solid fa-list"></i> عناصر السلة</h2>
                            <span class="portal-commerce-badge">{{ $count }} برنامج</span>
                        </div>
                        <div class="portal-panel__body portal-panel__body--padded">
                            <div class="commerce-list commerce-list--cart">
                                @foreach ($items as $item)
                                    @include('components.commerce.cart-item', ['item' => $item])
                                @endforeach
                            </div>
                        </div>
                    </section>
                </div>
                <div class="col-lg-4">
                    <aside class="portal-panel portal-commerce-summary sticky-top" style="top:100px">
                        <div class="portal-panel__head">
                            <h2 class="portal-panel__title"><i class="fa-solid fa-receipt"></i> ملخص السلة</h2>
                        </div>
                        <div class="portal-panel__body portal-panel__body--padded">
                            <div class="cart-summary-items">
                                @foreach ($items as $item)
                                    @php($meta = app(\App\Support\LegacyCourseCatalog::class)->resolveForItem($item))
                                    <div class="cart-summary-item">
                                        <a href="{{ $meta['url'] }}" class="cart-summary-item__title">{{ \Illuminate\Support\Str::limit($meta['title'], 42) }}</a>
                                        <span class="cart-summary-item__price" dir="ltr">{{ number_format((float) $item->price_snapshot, 0) }} ر.س</span>
                                    </div>
                                @endforeach
                            </div>

                            <div class="portal-commerce-summary__rows">
                                <div class="portal-commerce-summary__row">
                                    <span>عدد البرامج</span>
                                    <strong>{{ $count }}</strong>
                                </div>
                                <div class="portal-commerce-summary__row portal-commerce-summary__row--total">
                                    <span>الإجمالي</span>
                                    <strong dir="ltr">{{ number_format($total, 2) }} <small>ر.س</small></strong>
                                </div>
                            </div>
                            <a href="{{ route('checkout', ['locale' => $locale]) }}" class="btn btn-primary w-100 portal-commerce-summary__checkout">
                                <i class="fa-solid fa-credit-card"></i> إتمام الشراء
                            </a>
                            <p class="portal-commerce-summary__note">
                                <i class="fa-solid fa-shield-halved"></i>
                                دفع آمن عبر البوابات المعتمدة
                            </p>
                        </div>
                    </aside>
                </div>
            </div>
        @endif
    </div>
    @include('partials.portal.shell-end')
@else
    <div class="commerce-page commerce-cart-guest">
        <div class="page-content content py-0">
            <div class="container py-4">
                <section class="cart-guest-hero">
                    <div class="cart-guest-hero__main">
                        <span class="cart-guest-hero__icon" aria-hidden="true"><i class="fa-solid fa-cart-shopping"></i></span>
                        <div>
                            <h1 class="cart-guest-hero__title">سلة التسوق</h1>
                            <p class="cart-guest-hero__desc">سجّل بياناتك لإتمام الشراء — اضغط اسم البرنامج لعرض تفاصيله</p>
                        </div>
                    </div>
                    @if ($count > 0)
                        <div class="cart-guest-hero__stat">
                            <span>العناصر</span>
                            <strong>{{ $count }}</strong>
                        </div>
                    @endif
                </section>

                <div class="row g-4">
                    <div class="col-lg-8">
                        @if ($count === 0)
                            <div class="commerce-empty text-center">
                                <img src="{{ static_asset('assets/vendor/images/site-favicon.png') }}" alt="">
                                <p class="text-muted mt-3 mb-4">سلة التسوق فارغة حالياً</p>
                                <a href="{{ route('courses.index', ['locale' => $locale]) }}" class="btn btn-primary">اكتشف برامجنا</a>
                            </div>
                        @else
                            <div class="commerce-list commerce-list--cart">
                                @foreach ($items as $item)
                                    @include('components.commerce.cart-item', ['item' => $item])
                                @endforeach
                            </div>
                        @endif
                    </div>

                    @if ($count > 0)
                        <div class="col-lg-4">
                            <div class="commerce-summary-card commerce-guest-checkout p-4 cart-sticky-panel">
                                <div class="commerce-guest-checkout__head">
                                    <h5 class="mb-1 fw-bold">التسجيل وإتمام الشراء</h5>
                                    <p class="text-muted small mb-0">أنشئ حسابك الآن، وسنرسل بيانات الدخول إلى بريدك.</p>
                                </div>

                                <div class="commerce-guest-checkout__totals mt-3 mb-3 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">عدد البرامج</span>
                                        <strong>{{ $count }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>الإجمالي</span>
                                        <span class="summary-total" dir="ltr">{{ number_format($total, 0) }} ر.س</span>
                                    </div>
                                </div>

                                <form wire:submit="registerAndCheckout" class="commerce-guest-checkout__form">
                                    <div class="mb-3">
                                        <label class="form-label" for="guest-name">الاسم الكامل</label>
                                        <input id="guest-name" type="text" class="form-control" wire:model="guestName" placeholder="أدخل اسمك الكامل" autocomplete="name" required>
                                        @error('guestName')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="guest-email">البريد الإلكتروني</label>
                                        <input id="guest-email" type="email" class="form-control" wire:model="guestEmail" placeholder="example@email.com" dir="ltr" autocomplete="email" required>
                                        @error('guestEmail')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="guest-phone">رقم الجوال</label>
                                        <input id="guest-phone" type="tel" class="form-control" wire:model="guestPhone" placeholder="05xxxxxxxx" dir="ltr" autocomplete="tel" required>
                                        @error('guestPhone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>

                                    <button type="submit" class="btn btn-primary w-100 py-2 commerce-guest-checkout__submit" wire:loading.attr="disabled">
                                        <span wire:loading.remove wire:target="registerAndCheckout">
                                            <i class="fa-solid fa-user-plus"></i> تسجيل والمتابعة للسداد
                                        </span>
                                        <span wire:loading wire:target="registerAndCheckout">جاري إنشاء الحساب...</span>
                                    </button>
                                </form>

                                <ul class="commerce-guest-checkout__trust mt-3 mb-3">
                                    <li><i class="fa-solid fa-envelope-open-text"></i> كلمة مرور عشوائية تُرسل إلى بريدك</li>
                                    <li><i class="fa-solid fa-shield-halved"></i> دفع آمن عبر بوابات معتمدة أو تحويل بنكي</li>
                                    <li><i class="fa-solid fa-lock"></i> يتم تسجيل دخولك مباشرة بعد التسجيل</li>
                                </ul>

                                <p class="text-center small text-muted mb-0">
                                    لديك حساب؟
                                    <a class="fw-semibold" href="{{ route('login', ['locale' => $locale]) }}">تسجيل الدخول</a>
                                </p>
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
