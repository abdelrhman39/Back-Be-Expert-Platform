@php
    use App\Support\CmsBlockDefaults;
    use App\Support\CmsBlockRegistry;

    $blocksProp = $blocksLocale === 'en' ? 'blocksEn' : 'blocksAr';
    $blocks = $blocksLocale === 'en' ? $blocksEn : $blocksAr;
    $enabledCount = collect($blocks)->filter(fn ($block) => $block['enabled'] ?? true)->count();
    $type = $type ?? 'custom';
@endphp

<div class="cms-blocks-editor">
    <div class="cms-blocks-editor__hero">
        <div class="cms-blocks-editor__hero-text">
            <h3 class="cms-blocks-editor__title">أقسام الصفحة</h3>
            <p class="cms-blocks-editor__subtitle">
                رتّب الأقسام، اختر ما يظهر للزوار، وعدّل المحتوى. الدورات والدبلومات والأخبار تُحمَّل تلقائياً من مصادرها.
            </p>
        </div>
        <div class="cms-blocks-editor__stats">
            <span class="cms-blocks-editor__stat">
                <strong>{{ count($blocks) }}</strong>
                <small>قسم</small>
            </span>
            <span class="cms-blocks-editor__stat cms-blocks-editor__stat--success">
                <strong>{{ $enabledCount }}</strong>
                <small>ظاهر</small>
            </span>
            <span class="cms-blocks-editor__stat cms-blocks-editor__stat--muted">
                <strong>{{ count($blocks) - $enabledCount }}</strong>
                <small>مخفي</small>
            </span>
        </div>
    </div>

    <div class="cms-blocks-editor__toolbar">
        <div class="cms-blocks-editor__locale" role="group" aria-label="لغة البلوكات">
            <button type="button" @class(['is-active' => $blocksLocale === 'ar']) wire:click="$set('blocksLocale', 'ar')">عربي</button>
            <button type="button" @class(['is-active' => $blocksLocale === 'en']) wire:click="$set('blocksLocale', 'en')">EN</button>
        </div>
        <div class="cms-blocks-editor__toolbar-actions">
            <button type="button" class="cms-blocks-editor__ghost-btn" data-cms-blocks-expand-all>فتح الكل</button>
            <button type="button" class="cms-blocks-editor__ghost-btn" data-cms-blocks-collapse-all>طي الكل</button>
            @if (CmsBlockDefaults::usesBlocks($type ?? 'custom'))
                <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="resetBlocksDefaults">استعادة الافتراضي للنوع</button>
            @endif
        </div>
    </div>

    <div class="cms-blocks-editor__add">
        <select class="admin-control" wire:model="newBlockType">
            @foreach (CmsBlockRegistry::types() as $typeKey => $meta)
                <option value="{{ $typeKey }}">{{ $meta['label'] }}</option>
            @endforeach
        </select>
        <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="addBlock">إضافة بلوك</button>
    </div>

    <div class="cms-blocks-list">
        @forelse ($blocks as $index => $block)
            @php
                $type = $block['type'] ?? 'unknown';
                $typeLabel = CmsBlockRegistry::label($type);
                $data = $block['data'] ?? [];
                $enabled = $block['enabled'] ?? true;
                $panelId = 'cms-block-'.$blocksLocale.'-'.$index;
            @endphp
            <details
                class="cms-block-item"
                id="{{ $panelId }}"
                wire:key="cms-block-{{ $blocksLocale }}-{{ $block['id'] ?? $index }}"
                @class(['is-disabled' => ! $enabled])
                @if($index < 2) open @endif
            >
                <summary class="cms-block-item__head">
                    <span class="cms-block-item__collapse" aria-hidden="true">
                        <svg class="cms-block-item__chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <path d="M8 10l4 4 4-4"/>
                        </svg>
                        <span class="cms-block-item__collapse-label cms-block-item__collapse-label--open">طي</span>
                        <span class="cms-block-item__collapse-label cms-block-item__collapse-label--closed">فتح</span>
                    </span>

                    <span class="cms-block-item__index">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>

                    <div class="cms-block-item__meta">
                        <strong>{{ $typeLabel }}</strong>
                        <small dir="ltr">{{ $block['id'] ?? $type }}</small>
                    </div>

                    <span @class(['cms-block-item__status', 'is-visible' => $enabled, 'is-hidden' => ! $enabled])>
                        {{ $enabled ? 'ظاهر في الموقع' : 'مخفي عن الزوار' }}
                    </span>

                    <button
                        type="button"
                        @class(['cms-block-visibility-btn', 'is-visible' => $enabled, 'is-hidden-state' => ! $enabled])
                        wire:click.stop="toggleBlockEnabled({{ $index }})"
                        title="{{ $enabled ? 'إخفاء هذا القسم من الموقع' : 'إظهار هذا القسم في الموقع' }}"
                    >
                        @if ($enabled)
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            <span>إخفاء</span>
                        @else
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-6.5 0-10-7-10-7a20.8 20.8 0 0 1 5.06-6.24M9.9 4.24A10.94 10.94 0 0 1 12 5c6.5 0 10 7 10 7a20.75 20.75 0 0 1-3.57 4.72"/><path d="M1 1l22 22"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/></svg>
                            <span>إظهار</span>
                        @endif
                    </button>

                    <div class="cms-block-item__order">
                        <button type="button" class="cms-block-order-btn" wire:click.stop="moveBlockUp({{ $index }})" @disabled($index === 0) title="نقل للأعلى">
                            <span class="cms-block-order-btn__icon">↑</span>
                            <span class="cms-block-order-btn__label">أعلى</span>
                        </button>
                        <button type="button" class="cms-block-order-btn" wire:click.stop="moveBlockDown({{ $index }})" @disabled($index === count($blocks) - 1) title="نقل للأسفل">
                            <span class="cms-block-order-btn__icon">↓</span>
                            <span class="cms-block-order-btn__label">أسفل</span>
                        </button>
                        <button
                            type="button"
                            class="cms-block-order-btn cms-block-order-btn--danger"
                            wire:click.stop="removeBlock({{ $index }})"
                            wire:confirm="حذف هذا البلوك؟"
                            title="حذف البلوك"
                        >
                            <span class="cms-block-order-btn__icon">×</span>
                            <span class="cms-block-order-btn__label">حذف</span>
                        </button>
                    </div>
                </summary>

                <div class="cms-block-item__body">
                    @switch($type)
                        @case('hero')
                            <div class="admin-field"><label>العنوان الرئيسي</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.title"></div>
                            <div class="admin-field"><label>سطر فرعي 1</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.subtitle_lines.0"></div>
                            <div class="admin-field"><label>سطر فرعي 2</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.subtitle_lines.1"></div>
                            <div class="admin-field admin-field--wide">
                                @include('partials.admin.media-field', [
                                    'wireModel' => $blocksProp.'.'.$index.'.data.image',
                                    'id' => 'cms-hero-'.$index,
                                    'label' => 'صورة الخلفية',
                                    'previewUrl' => ! empty($data['image']) ? cms_media_url($data['image']) : null,
                                ])
                            </div>
                            @break

                        @case('catalog_section')
                            <p class="admin-field-hint">مصدر البيانات: <code dir="ltr">{{ $data['source'] ?? '' }}</code> — يُدار من كatalog الدورات.</p>
                            @break

                        @case('cards_grid')
                        @case('features_grid')
                            @foreach ($data['items'] ?? [] as $itemIndex => $item)
                                <fieldset class="cms-block-subgroup">
                                    <legend>بطاقة {{ $itemIndex + 1 }}</legend>
                                    <div class="admin-field"><label>العنوان</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.items.{{ $itemIndex }}.title"></div>
                                    <div class="admin-field"><label>النص</label><textarea class="admin-control" rows="3" wire:model="{{ $blocksProp }}.{{ $index }}.data.items.{{ $itemIndex }}.body"></textarea></div>
                                    <div class="admin-field"><label>الأيقونة (مسار assets)</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.items.{{ $itemIndex }}.icon" dir="ltr"></div>
                                </fieldset>
                            @endforeach
                            @if ($type === 'features_grid')
                                <div class="admin-field admin-field--wide"><label>عنوان القسم</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.title"></div>
                            @endif
                            @break

                        @case('image_cards')
                            <div class="admin-field"><label>عنوان القسم</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.title"></div>
                            <div class="admin-field"><label>نص زر «جميع البرامج»</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.cta_label"></div>
                            <div class="admin-field"><label>رابط الزر (اسم مسار أو URL)</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.cta_url" dir="ltr" placeholder="courses.index أو /ar/courses"></div>
                            @foreach ($data['items'] ?? [] as $itemIndex => $item)
                                <fieldset class="cms-block-subgroup">
                                    <legend>برنامج {{ $itemIndex + 1 }}</legend>
                                    <div class="admin-field"><label>العنوان</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.items.{{ $itemIndex }}.title"></div>
                                    <div class="admin-field">
                                        @include('partials.admin.media-field', [
                                            'wireModel' => $blocksProp.'.'.$index.'.data.items.'.$itemIndex.'.image',
                                            'id' => 'cms-imgcard-'.$index.'-'.$itemIndex,
                                            'label' => 'الصورة',
                                            'previewUrl' => ! empty($item['image']) ? cms_media_url($item['image']) : null,
                                        ])
                                    </div>
                                    <div class="admin-field"><label>الرابط</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.items.{{ $itemIndex }}.url" dir="ltr"></div>
                                </fieldset>
                            @endforeach
                            @break

                        @case('logo_carousel')
                            <div class="admin-field admin-field--wide">
                                <label>عنوان القسم</label>
                                <input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.title">
                            </div>
                            <div class="cms-items-manager">
                                <div class="cms-items-manager__head">
                                    <strong>الشعارات</strong>
                                    <span>{{ count($data['logos'] ?? []) }} عنصر — بدون حد أقصى</span>
                                    <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="addLogoItem({{ $index }})">+ إضافة شعار</button>
                                </div>
                                @forelse ($data['logos'] ?? [] as $logoIndex => $logo)
                                    <article class="cms-item-card" wire:key="logo-{{ $index }}-{{ $logoIndex }}-{{ md5(($logo['image'] ?? '').($logo['alt'] ?? '')) }}">
                                        <div class="cms-item-card__preview">
                                            @if (! empty($logo['image']))
                                                <img src="{{ cms_media_url($logo['image']) }}" alt="">
                                            @else
                                                <span>بدون صورة</span>
                                            @endif
                                        </div>
                                        <div class="cms-item-card__fields">
                                            <div class="admin-field">
                                                <label>النص البديل (Alt)</label>
                                                <input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.logos.{{ $logoIndex }}.alt" placeholder="اسم الجهة أو الشريك">
                                            </div>
                                            <div class="admin-field">
                                                @include('partials.admin.media-field', [
                                                    'wireModel' => $blocksProp.'.'.$index.'.data.logos.'.$logoIndex.'.image',
                                                    'id' => 'cms-logo-'.$index.'-'.$logoIndex,
                                                    'label' => 'صورة الشعار',
                                                    'previewUrl' => ! empty($logo['image']) ? cms_media_url($logo['image']) : null,
                                                    'placeholder' => 'assets/… أو /storage/… أو https://…',
                                                ])
                                            </div>
                                            <div class="admin-field">
                                                <label>رفع مباشر (بديل)</label>
                                                <input
                                                    type="file"
                                                    class="admin-control"
                                                    accept="image/*"
                                                    wire:click="prepareCmsImageUpload({{ $index }}, 'logos', {{ $logoIndex }}, 'image')"
                                                    wire:model="cmsImageFile"
                                                >
                                            </div>
                                        </div>
                                        <div class="cms-item-card__actions">
                                            <button type="button" class="cms-item-card__btn" wire:click="moveLogoItem({{ $index }}, {{ $logoIndex }}, 'up')" title="أعلى">↑</button>
                                            <button type="button" class="cms-item-card__btn" wire:click="moveLogoItem({{ $index }}, {{ $logoIndex }}, 'down')" title="أسفل">↓</button>
                                            <button type="button" class="cms-item-card__btn cms-item-card__btn--danger" wire:click="removeLogoItem({{ $index }}, {{ $logoIndex }})" wire:confirm="حذف هذا الشعار؟">حذف</button>
                                        </div>
                                    </article>
                                @empty
                                    <p class="cms-items-manager__empty">لا توجد شعارات بعد. اضغط «إضافة شعار» للبدء.</p>
                                @endforelse
                            </div>
                            @break

                        @case('news_cards')
                            <div class="admin-field"><label>عنوان القسم</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.title"></div>
                            <div class="admin-field"><label>شارة التصنيف</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.badge"></div>
                            <div class="admin-field">
                                <label>عدد المقالات في الرئيسية</label>
                                <input type="number" class="admin-control" min="1" max="12" wire:model="{{ $blocksProp }}.{{ $index }}.data.limit">
                            </div>
                            <p class="admin-field-hint">
                                المقالات تُدار من
                                <a href="{{ route('admin.articles') }}">الأخبار والفعاليات</a>
                                — يُعرض أحدث المقالات المنشورة تلقائياً.
                            </p>
                            @break

                        @case('testimonials')
                            <div class="admin-field admin-field--wide">
                                <label>عنوان القسم</label>
                                <input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.title">
                            </div>
                            <div class="cms-items-manager">
                                <div class="cms-items-manager__head">
                                    <strong>آراء العملاء</strong>
                                    <span>{{ count($data['items'] ?? []) }} رأي — بدون حد أقصى</span>
                                    <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="addTestimonialItem({{ $index }})">+ إضافة رأي</button>
                                </div>
                                @forelse ($data['items'] ?? [] as $itemIndex => $item)
                                    <article class="cms-item-card cms-item-card--testimonial" wire:key="testimonial-{{ $index }}-{{ $itemIndex }}">
                                        <div class="cms-item-card__preview cms-item-card__preview--avatar">
                                            @if (! empty($item['avatar']))
                                                <img src="{{ cms_media_url($item['avatar']) }}" alt="">
                                            @else
                                                <span>بدون صورة</span>
                                            @endif
                                        </div>
                                        <div class="cms-item-card__fields">
                                            <div class="admin-field admin-field--wide">
                                                <label>نص الرأي</label>
                                                <textarea class="admin-control" rows="4" wire:model="{{ $blocksProp }}.{{ $index }}.data.items.{{ $itemIndex }}.quote" placeholder="اكتب رأي العميل هنا بشكل واضح ومقروء…"></textarea>
                                            </div>
                                            <div class="admin-field">
                                                <label>الاسم</label>
                                                <input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.items.{{ $itemIndex }}.name">
                                            </div>
                                            <div class="admin-field">
                                                <label>المسمى / الجهة</label>
                                                <input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.items.{{ $itemIndex }}.role" placeholder="طالب، خريج، جهة…">
                                            </div>
                                            <div class="admin-field">
                                                <label>التقييم (1–5)</label>
                                                <input type="number" class="admin-control" min="1" max="5" wire:model="{{ $blocksProp }}.{{ $index }}.data.items.{{ $itemIndex }}.rating">
                                            </div>
                                            <div class="admin-field">
                                                @include('partials.admin.media-field', [
                                                    'wireModel' => $blocksProp.'.'.$index.'.data.items.'.$itemIndex.'.avatar',
                                                    'id' => 'cms-avatar-'.$index.'-'.$itemIndex,
                                                    'label' => 'صورة الشخص',
                                                    'previewUrl' => ! empty($item['avatar']) ? cms_media_url($item['avatar']) : null,
                                                    'placeholder' => 'assets/… أو /storage/…',
                                                ])
                                            </div>
                                            <div class="admin-field">
                                                <label>رفع صورة</label>
                                                <input
                                                    type="file"
                                                    class="admin-control"
                                                    accept="image/*"
                                                    wire:click="prepareCmsImageUpload({{ $index }}, 'items', {{ $itemIndex }}, 'avatar')"
                                                    wire:model="cmsImageFile"
                                                >
                                            </div>
                                        </div>
                                        <div class="cms-item-card__actions">
                                            <button type="button" class="cms-item-card__btn" wire:click="moveTestimonialItem({{ $index }}, {{ $itemIndex }}, 'up')" title="أعلى">↑</button>
                                            <button type="button" class="cms-item-card__btn" wire:click="moveTestimonialItem({{ $index }}, {{ $itemIndex }}, 'down')" title="أسفل">↓</button>
                                            <button type="button" class="cms-item-card__btn cms-item-card__btn--danger" wire:click="removeTestimonialItem({{ $index }}, {{ $itemIndex }})" wire:confirm="حذف هذا الرأي؟">حذف</button>
                                        </div>
                                    </article>
                                @empty
                                    <p class="cms-items-manager__empty">لا توجد آراء بعد. اضغط «إضافة رأي» للبدء.</p>
                                @endforelse
                            </div>
                            @break

                        @case('faq')
                            <div class="admin-field"><label>عنوان القسم</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.title"></div>
                            @foreach ($data['items'] ?? [] as $itemIndex => $item)
                                <fieldset class="cms-block-subgroup">
                                    <legend>سؤال {{ $itemIndex + 1 }}</legend>
                                    <div class="admin-field"><label>السؤال</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.items.{{ $itemIndex }}.question"></div>
                                    <div class="admin-field"><label>الجواب</label><textarea class="admin-control" rows="2" wire:model="{{ $blocksProp }}.{{ $index }}.data.items.{{ $itemIndex }}.answer"></textarea></div>
                                </fieldset>
                            @endforeach
                            @break

                        @case('stats')
                            <div class="admin-field"><label>اسم المنصة (في العنوان)</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.platform_name"></div>
                            <div class="admin-field"><label>بادئة العنوان (اختياري)</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.title_prefix" placeholder="مثل: منصة"></div>
                            <div class="admin-field"><label>لاحقة العنوان</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.title_suffix" placeholder="في أرقام"></div>
                            @foreach ($data['items'] ?? [] as $itemIndex => $item)
                                <fieldset class="cms-block-subgroup">
                                    <legend>عداد {{ $itemIndex + 1 }}</legend>
                                    <div class="admin-field"><label>التسمية</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.items.{{ $itemIndex }}.label"></div>
                                    <div class="admin-field"><label>القيمة</label><input type="number" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.items.{{ $itemIndex }}.value"></div>
                                    <div class="admin-field"><label>اللاحقة</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.items.{{ $itemIndex }}.suffix" placeholder="+"></div>
                                </fieldset>
                            @endforeach
                            @break

                        @case('rich_text_split')
                            <div class="admin-field"><label>العنوان</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.title"></div>
                            <div class="admin-field"><label>الصورة</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.image" dir="ltr"></div>
                            @foreach ($data['paragraphs'] ?? [] as $pIndex => $paragraph)
                                <div class="admin-field"><label>فقرة {{ $pIndex + 1 }}</label><textarea class="admin-control" rows="3" wire:model="{{ $blocksProp }}.{{ $index }}.data.paragraphs.{{ $pIndex }}"></textarea></div>
                            @endforeach
                            @break

                        @case('download_cta')
                            <div class="admin-field"><label>العنوان</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.title"></div>
                            <div class="admin-field"><label>الوصف</label><textarea class="admin-control" rows="2" wire:model="{{ $blocksProp }}.{{ $index }}.data.description"></textarea></div>
                            <div class="admin-field"><label>نص الزر</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.button_label"></div>
                            <div class="admin-field"><label>رابط الملف</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.file_url" dir="ltr"></div>
                            @break

                        @case('breadcrumb')
                            <div class="admin-field"><label>عنوان الصفحة</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.title"></div>
                            <div class="admin-field"><label>عنوان الرابط الأب</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.parent_label"></div>
                            <div class="admin-field admin-field--wide">
                                @include('partials.admin.media-field', [
                                    'wireModel' => $blocksProp.'.'.$index.'.data.background_image',
                                    'id' => 'cms-bc-'.$index,
                                    'label' => 'صورة الخلفية',
                                    'previewUrl' => ! empty($data['background_image']) ? cms_media_url($data['background_image']) : null,
                                ])
                            </div>
                            @break

                        @case('contact_intro')
                            <div class="admin-field"><label>العنوان</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.title"></div>
                            <div class="admin-field"><label>النص التمهيدي</label><textarea class="admin-control" rows="4" wire:model="{{ $blocksProp }}.{{ $index }}.data.body"></textarea></div>
                            @foreach ($data['buttons'] ?? [] as $buttonIndex => $button)
                                <fieldset class="cms-block-subgroup">
                                    <legend>زر {{ $buttonIndex + 1 }}</legend>
                                    <div class="admin-field"><label>النص</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.buttons.{{ $buttonIndex }}.label"></div>
                                    <div class="admin-field">
                                        <label>النمط</label>
                                        <select class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.buttons.{{ $buttonIndex }}.style">
                                            <option value="primary">أساسي</option>
                                            <option value="outline-primary">حدود أساسي</option>
                                            <option value="outline-secondary">حدود ثانوي</option>
                                            <option value="secondary">ثانوي</option>
                                        </select>
                                    </div>
                                    <div class="admin-field">
                                        <label>نوع الرابط</label>
                                        <select class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.buttons.{{ $buttonIndex }}.link_type">
                                            <option value="route">مسار Laravel (route)</option>
                                            <option value="url">رابط مباشر</option>
                                        </select>
                                    </div>
                                    <div class="admin-field">
                                        <label>الرابط / اسم المسار</label>
                                        <input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.buttons.{{ $buttonIndex }}.link" dir="ltr" placeholder="support.faq أو https://...">
                                        <div class="admin-field-hint">أمثلة: <code dir="ltr">support.faq</code>، <code dir="ltr">support.ticket.new</code>، <code dir="ltr">contact</code></div>
                                    </div>
                                </fieldset>
                            @endforeach
                            @break

                        @case('contact_channels')
                            @foreach ($data['items'] ?? [] as $itemIndex => $item)
                                <fieldset class="cms-block-subgroup">
                                    <legend>قناة {{ $itemIndex + 1 }}</legend>
                                    <label class="admin-checkbox">
                                        <input type="checkbox" wire:model="{{ $blocksProp }}.{{ $index }}.data.items.{{ $itemIndex }}.enabled">
                                        <span>مفعّلة</span>
                                    </label>
                                    <div class="admin-field">
                                        <label>النوع</label>
                                        <select class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.items.{{ $itemIndex }}.kind">
                                            <option value="email">بريد</option>
                                            <option value="phone">جوال</option>
                                            <option value="whatsapp">واتساب</option>
                                            <option value="address">عنوان</option>
                                            <option value="custom">مخصص</option>
                                        </select>
                                    </div>
                                    <div class="admin-field"><label>العنوان</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.items.{{ $itemIndex }}.label"></div>
                                    <div class="admin-field"><label>القيمة</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.items.{{ $itemIndex }}.value" dir="ltr"></div>
                                    <div class="admin-field">
                                        <label>نوع الأيقونة</label>
                                        <select class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.items.{{ $itemIndex }}.icon_type">
                                            <option value="image">صورة (assets)</option>
                                            <option value="fontawesome">Font Awesome</option>
                                        </select>
                                    </div>
                                    <div class="admin-field"><label>الأيقونة</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.items.{{ $itemIndex }}.icon" dir="ltr" placeholder="assets/contact-mail.svg أو fa-brands fa-whatsapp"></div>
                                    <div class="admin-field"><label>رابط (للنوع المخصص فقط)</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.items.{{ $itemIndex }}.link_url" dir="ltr"></div>
                                </fieldset>
                            @endforeach
                            @break

                        @case('contact_map_form')
                            <label class="admin-checkbox">
                                <input type="checkbox" wire:model="{{ $blocksProp }}.{{ $index }}.data.show_map">
                                <span>إظهار الخريطة</span>
                            </label>
                            <div class="admin-field"><label>رابط embed للخريطة</label><textarea class="admin-control" rows="2" wire:model="{{ $blocksProp }}.{{ $index }}.data.map_embed_url" dir="ltr"></textarea></div>
                            <div class="admin-field"><label>عنوان iframe الخريطة</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.map_iframe_title"></div>
                            <div class="admin-field"><label>معرّف مرساة النموذج (anchor)</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.form_anchor_id" dir="ltr" placeholder="contact-us-Form"></div>
                            <div class="admin-field"><label>عنوان النموذج</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.form_title"></div>
                            <div class="admin-field"><label>بريد استقبال الرسائل</label><input type="email" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.support_email" dir="ltr"></div>
                            <div class="admin-field"><label>مسار تذكرة الشكاوى</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.complain_redirect_route" dir="ltr" placeholder="support.ticket.new"></div>
                            <div class="admin-field"><label>قيمة خيار «شكوى»</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.complain_reason_value" dir="ltr"></div>
                            <div class="admin-field"><label>تسمية حقل الاسم</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.field_name_label"></div>
                            <div class="admin-field"><label>تسمية البريد</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.field_email_label"></div>
                            <div class="admin-field"><label>تسمية الجوال</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.field_phone_label"></div>
                            <div class="admin-field"><label>Placeholder الجوال</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.field_phone_placeholder" dir="ltr"></div>
                            <div class="admin-field"><label>تسمية سبب التواصل</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.field_reason_label"></div>
                            <div class="admin-field"><label>تسمية الرسالة</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.field_message_label"></div>
                            <div class="admin-field"><label>تلميح الرسالة</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.field_message_hint"></div>
                            <div class="admin-field"><label>الحد الأقصى للأحرف</label><input type="number" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.message_max_length"></div>
                            <div class="admin-field"><label>نص زر الإرسال</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.submit_label"></div>
                            @foreach ($data['reasons'] ?? [] as $reasonIndex => $reason)
                                <fieldset class="cms-block-subgroup">
                                    <legend>سبب {{ $reasonIndex + 1 }}</legend>
                                    <div class="admin-field"><label>القيمة (value)</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.reasons.{{ $reasonIndex }}.value" dir="ltr"></div>
                                    <div class="admin-field"><label>التسمية</label><input type="text" class="admin-control" wire:model="{{ $blocksProp }}.{{ $index }}.data.reasons.{{ $reasonIndex }}.label"></div>
                                </fieldset>
                            @endforeach
                            @break

                        @default
                            <p class="admin-field-hint">نوع بلوك: {{ $type }}</p>
                    @endswitch
                </div>
            </details>
        @empty
            <div class="cms-blocks-empty">
                <span class="cms-blocks-empty__icon">▦</span>
                <p>لا توجد بلوكات بعد. أضف بلوكاً من القائمة أعلاه، أو استعد الافتراضي إن وُجد لنوع الصفحة.</p>
                @if (CmsBlockDefaults::usesBlocks($type))
                    <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="resetBlocksDefaults">استعادة الافتراضي</button>
                @endif
            </div>
        @endforelse
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('click', (event) => {
                const expandAll = event.target.closest('[data-cms-blocks-expand-all]');
                const collapseAll = event.target.closest('[data-cms-blocks-collapse-all]');
                const editor = event.target.closest('.cms-blocks-editor');

                if (! editor) {
                    return;
                }

                if (expandAll) {
                    editor.querySelectorAll('.cms-block-item').forEach((item) => item.setAttribute('open', 'open'));
                }

                if (collapseAll) {
                    editor.querySelectorAll('.cms-block-item').forEach((item) => item.removeAttribute('open'));
                }
            });
        </script>
    @endpush
@endonce
