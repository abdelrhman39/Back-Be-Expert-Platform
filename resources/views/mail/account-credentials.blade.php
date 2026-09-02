<x-mail::message>
# مرحباً {{ $user->displayName() }}

تم إنشاء حسابك بنجاح في مركز التعلم المستمر.

## بيانات الدخول

- **البريد الإلكتروني:** {{ $user->email }}
@if ($user->phone)
- **الجوال:** {{ $user->phone }}
@endif
- **كلمة المرور المؤقتة:** `{{ $plainPassword }}`

نوصي بتغيير كلمة المرور من الإعدادات بعد أول دخول.

@if (count($cartItems) > 0)
## محتويات سلة التسوق

@foreach ($cartItems as $item)
- {{ $item['title'] }}@if (! empty($item['price'])) — {{ number_format((float) $item['price'], 0) }} ر.س @endif

@endforeach

**الإجمالي:** {{ number_format($cartTotal, 0) }} ر.س
@endif

@if ($checkoutUrl)
<x-mail::button :url="$checkoutUrl">
إتمام الشراء
</x-mail::button>
@elseif ($loginUrl)
<x-mail::button :url="$loginUrl">
تسجيل الدخول
</x-mail::button>
@endif

شكراً لاختيارك منصتنا،<br>
{{ config('app.name') }}
</x-mail::message>
