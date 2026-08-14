@php
    $locale = app()->getLocale();
    $price = $this->displayPrice();
    $courseUrl = route('courses.show', ['locale' => $locale, 'course' => $course->showSlug()]);
    $deliveryLabel = $course->delivery_type === 'online' ? 'عن بعد' : 'حضوري';
@endphp

<div @class(['course-enroll-form', 'course-enroll-form--compact' => $compact])>
    @if (! $compact)
        <div class="container">
            <div class="row g-4 align-items-start">
                <div class="col-lg-4 order-lg-2">
                    <aside class="course-enroll-summary sticky-top">
                        <div class="course-enroll-summary__poster">
                            <img src="{{ $course->posterUrl() }}" alt="{{ $course->displayTitle() }}" class="course-enroll-summary__img">
                            <span class="course-enroll-summary__badge">{{ $deliveryLabel }}</span>
                        </div>

                        <div class="course-enroll-summary__body">
                            <p class="course-enroll-summary__eyebrow">البرنامج المختار</p>
                            <h2 class="course-enroll-summary__title">{{ $course->displayTitle() }}</h2>

                            @if (($schedule['hours'] ?? null) || ($schedule['days'] ?? null))
                                <ul class="course-enroll-summary__meta">
                                    @if ($schedule['hours'] ?? null)
                                        <li>
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M12 7V12L14.5 10.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <span>{{ $schedule['hours'] }} ساعة تدريبية</span>
                                        </li>
                                    @endif
                                    @if ($schedule['days'] ?? null)
                                        <li>
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M8 2V6M16 2V6M3 10H21M5 4H19C20.1046 4 21 4.89543 21 6V20C21 21.1046 20.1046 22 19 22H5C3.89543 22 3 21.1046 3 20V6C3 4.89543 3.89543 4 5 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <span>{{ $schedule['days'] }} يوم</span>
                                        </li>
                                    @endif
                                </ul>
                            @endif

                            @if ($price !== null)
                                <div class="course-enroll-summary__price">
                                    <span class="course-enroll-summary__price-label">الرسوم</span>
                                    <strong>{{ number_format($price, 0) }} @include('partials.catalog.sar-icon')</strong>
                                </div>
                            @endif

                            <a href="{{ $courseUrl }}" class="course-enroll-summary__link">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M14 6L8 12L14 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                عرض تفاصيل البرنامج
                            </a>
                        </div>
                    </aside>
                </div>

                <div class="col-lg-8 order-lg-1">
    @endif

    <form wire:submit="enroll" class="course-enroll-fields">
        @if ($compact)
            <div class="course-enroll-panel__intro">
                <h3 class="course-enroll-panel__title">التسجيل والدفع</h3>
                <p class="course-enroll-panel__hint">أدخل بياناتك للمتابعة إلى السداد</p>
            </div>

            @if ($price !== null)
                <div class="course-enroll-checkout__total course-enroll-checkout__total--top">
                    <div>
                        <span class="course-enroll-checkout__total-label">المبلغ المستحق</span>
                        <span class="course-enroll-checkout__total-hint">شامل رسوم البرنامج</span>
                    </div>
                    <strong class="course-enroll-checkout__total-amount">
                        {{ number_format($price, 0) }}
                        @include('partials.catalog.sar-icon')
                    </strong>
                </div>
            @endif
        @else
            <div class="course-enroll-fields__head">
                <div class="course-enroll-fields__head-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                        <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div>
                    <h2 class="course-enroll-fields__title">بيانات التسجيل</h2>
                    <p class="course-enroll-fields__hint">أدخل بياناتك للمتابعة إلى السداد عبر بوابات الدفع أو التحويل البنكي.</p>
                </div>
            </div>
        @endif

        <div class="course-enroll-fields__grid">
            <div class="course-enroll-field">
                <label class="form-label" for="enroll-name-{{ $course->id }}">الاسم الكامل</label>
                <input type="text" id="enroll-name-{{ $course->id }}" class="form-control" wire:model="name" placeholder="أدخل اسمك الكامل" required autocomplete="name">
                @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="course-enroll-field">
                <label class="form-label" for="enroll-email-{{ $course->id }}">البريد الإلكتروني</label>
                <input type="email" id="enroll-email-{{ $course->id }}" class="form-control" wire:model="email" placeholder="example@email.com" dir="ltr" required autocomplete="email">
                @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="course-enroll-field course-enroll-field--full">
                <label class="form-label" for="enroll-phone-{{ $course->id }}">رقم الجوال</label>
                <input type="tel" id="enroll-phone-{{ $course->id }}" class="form-control" wire:model="phone" placeholder="05xxxxxxxx" dir="ltr" required autocomplete="tel">
                @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
        </div>

        @if ($this->showDeliveryChoice())
            <div class="course-enroll-delivery">
                <span class="form-label d-block mb-2">نوع التدريب</span>
                <div class="course-enroll-delivery__options">
                    @if ($this->hasOnlinePrice())
                        <label @class(['course-enroll-delivery__option', 'is-selected' => $deliveryType === 'online'])>
                            <input type="radio" wire:model.live="deliveryType" value="online" class="visually-hidden">
                            <span class="course-enroll-delivery__check" aria-hidden="true"></span>
                            <span class="course-enroll-delivery__icon" aria-hidden="true">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                    <path d="M9.75 17L8 21L12 19.5L16 21L14.25 17M12 3V14M8 8H16C17.1046 8 18 8.89543 18 10V14C18 15.1046 17.1046 16 16 16H8C6.89543 16 6 15.1046 6 14V10C6 8.89543 6.89543 8 8 8Z" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span class="course-enroll-delivery__body">
                                <strong>عن بعد</strong>
                                <span>{{ number_format((float) $course->price_online, 0) }} ر.س</span>
                            </span>
                        </label>
                    @endif
                    @if ($this->hasOnsitePrice())
                        <label @class(['course-enroll-delivery__option', 'is-selected' => in_array($deliveryType, ['onsite', 'offline'], true)])>
                            <input type="radio" wire:model.live="deliveryType" value="onsite" class="visually-hidden">
                            <span class="course-enroll-delivery__check" aria-hidden="true"></span>
                            <span class="course-enroll-delivery__icon" aria-hidden="true">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                    <path d="M3 21H21M5 21V7L12 3L19 7V21M9 21V13H15V21" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span class="course-enroll-delivery__body">
                                <strong>حضوري</strong>
                                <span>{{ number_format((float) $course->price_onsite, 0) }} ر.س</span>
                            </span>
                        </label>
                    @endif
                </div>
                @error('deliveryType')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
        @else
            <div class="course-enroll-delivery-fixed">
                <span class="course-enroll-delivery-fixed__label">نوع التدريب</span>
                <strong>{{ $course->deliveryModesLabel() }}</strong>
            </div>
        @endif

        @if (! $compact && $price !== null)
            <div class="course-enroll-total">
                <div>
                    <span class="course-enroll-total__label">الإجمالي المستحق</span>
                    <span class="course-enroll-total__hint">شامل رسوم البرنامج</span>
                </div>
                <strong class="course-enroll-total__amount">{{ number_format($price, 0) }} @include('partials.catalog.sar-icon')</strong>
            </div>
        @endif

        <div @class(['course-enroll-checkout', 'course-enroll-checkout--compact' => $compact])>
            <div class="course-enroll-actions">
                @if (! $compact)
                    <a href="{{ $courseUrl }}" class="btn btn-outline-secondary course-enroll-actions__back">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M14 6L8 12L14 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        العودة للبرنامج
                    </a>
                @endif

                <button type="submit" class="btn btn-primary course-enroll-panel__submit course-enroll-actions__submit" wire:loading.attr="disabled" wire:target="enroll">
                    <span class="course-enroll-checkout__submit-inner" wire:loading.remove wire:target="enroll">
                        <span>متابعة إلى السداد</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M10 6L4 12L10 18M4 12H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span class="course-enroll-checkout__submit-busy" wire:loading.inline-flex wire:target="enroll">جاري التحضير...</span>
                </button>
            </div>

            <ul class="course-enroll-trust" aria-label="مزايا السداد">
                <li class="course-enroll-trust__item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 22C12 22 20 18 20 12V5L12 2L4 5V12C4 18 12 22 12 22Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>دفع آمن ومشفّر</span>
                </li>
                <li class="course-enroll-trust__item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M3 10H21M7 15H8M12 15H13M6 19H18C19.1046 19 20 18.1046 20 17V7C20 5.89543 19.1046 5 18 5H6C4.89543 5 4 5.89543 4 7V17C4 18.1046 4.89543 19 6 19Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>بوابات معتمدة</span>
                </li>
                <li class="course-enroll-trust__item">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 3V21M8 7H14.5C15.8807 7 17 8.11929 17 9.5C17 10.8807 15.8807 12 14.5 12H8M8 12H15.5C16.8807 12 18 13.1193 18 14.5C18 15.8807 16.8807 17 15.5 17H8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>تحويل بنكي متاح</span>
                </li>
            </ul>
        </div>
    </form>

    @if (! $compact)
                </div>
            </div>
        </div>
    @endif
</div>
