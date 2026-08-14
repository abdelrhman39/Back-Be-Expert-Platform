@php
    use App\Support\AcademicProgramOptions;
@endphp

<div class="admin-form-section">
    <h3 class="admin-form-section__title">البيانات الأساسية</h3>
    <div class="admin-filter-grid" style="grid-template-columns: repeat(2, 1fr);">
        <div class="admin-field">
            <label for="nameAr">اسم البرنامج (عربي) *</label>
            <input id="nameAr" type="text" class="admin-control" wire:model="nameAr">
            @error('nameAr')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
        </div>
        <div class="admin-field">
            <label for="nameEn">اسم البرنامج (إنجليزي)</label>
            <input id="nameEn" type="text" class="admin-control" wire:model="nameEn">
        </div>
        <div class="admin-field">
            <label for="nameOnCertificate">اسم البرنامج في الشهادة</label>
            <input id="nameOnCertificate" type="text" class="admin-control" wire:model="nameOnCertificate">
        </div>
        <div class="admin-field">
            <label for="code">رمز البرنامج *</label>
            <input id="code" type="text" class="admin-control" wire:model="code" dir="ltr">
            @error('code')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
        </div>
        <div class="admin-field">
            <label for="symbol">الرمز الداخلي</label>
            <input id="symbol" type="text" class="admin-control" wire:model="symbol">
        </div>
        <div class="admin-field">
            <label for="type">نوع البرنامج *</label>
            <select id="type" class="admin-control" wire:model="type">
                @foreach (AcademicProgramOptions::types() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="admin-field">
            <label for="status">الحالة *</label>
            <select id="status" class="admin-control" wire:model="status">
                @foreach (AcademicProgramOptions::statuses() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="admin-field">
            <label for="studyStatus">حالة الدراسة (نص للعرض)</label>
            <input id="studyStatus" type="text" class="admin-control" wire:model="studyStatus" placeholder="مثال: فعال — دفعة 2025/2026">
        </div>
    </div>
</div>

<div class="admin-form-section">
    <h3 class="admin-form-section__title">المدة والجدولة</h3>
    <div class="admin-filter-grid" style="grid-template-columns: repeat(3, 1fr);">
        <div class="admin-field">
            <label for="durationMonths">مدة البرنامج (أشهر)</label>
            <select id="durationMonths" class="admin-control" wire:model="durationMonths">
                <option value="">—</option>
                @foreach (AcademicProgramOptions::durationMonthsOptions() as $months)
                    <option value="{{ $months }}">{{ $months }} شهر</option>
                @endforeach
            </select>
        </div>
        <div class="admin-field">
            <label for="durationLabel">وصف المدة</label>
            <input id="durationLabel" type="text" class="admin-control" wire:model="durationLabel" placeholder="عام دراسي / 6 أشهر">
        </div>
        <div class="admin-field">
            <label for="startDate">تاريخ البدء</label>
            <input id="startDate" type="date" class="admin-control" wire:model="startDate">
        </div>
    </div>
</div>

<div class="admin-form-section">
    <h3 class="admin-form-section__title">المنسق والتواصل</h3>
    <div class="admin-filter-grid" style="grid-template-columns: repeat(2, 1fr);">
        <div class="admin-field">
            <label for="coordinator">منسق البرنامج</label>
            <input id="coordinator" type="text" class="admin-control" wire:model="coordinator">
        </div>
        <div class="admin-field">
            <label for="city">المدينة</label>
            <input id="city" type="text" class="admin-control" wire:model="city">
        </div>
        <div class="admin-field">
            <label for="email">البريد الإلكتروني</label>
            <input id="email" type="email" class="admin-control" wire:model="email" dir="ltr">
            @error('email')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
        </div>
        <div class="admin-field">
            <label for="phone">الهاتف</label>
            <input id="phone" type="text" class="admin-control" wire:model="phone" dir="ltr">
        </div>
        <div class="admin-field admin-field--wide">
            <label for="mediaUrl">رابط صفحة البرنامج</label>
            <input id="mediaUrl" type="url" class="admin-control" wire:model="mediaUrl" dir="ltr" placeholder="https://">
        </div>
        <div class="admin-field admin-field--wide">
            @include('partials.admin.media-field', [
                'wireModel' => 'posterImage',
                'id' => 'posterImage',
                'label' => 'صورة البوستر (اختياري)',
                'hint' => 'اتركه فارغاً لاستخدام الصورة الافتراضية من إعدادات المنصة.',
                'previewUrl' => filled($posterImage) ? resolve_poster_url($posterImage) : null,
                'placeholder' => 'assets/... أو /storage/...',
            ])
        </div>
    </div>
</div>

<div class="admin-form-section">
    <h3 class="admin-form-section__title">الوصف والمهارات</h3>
    <div class="admin-field">
        <label for="summary">نبذة عن البرنامج</label>
        <textarea id="summary" class="admin-control" rows="5" wire:model="summary"></textarea>
    </div>
    <div class="admin-field">
        <label for="skillsText">المهارات المكتسبة (سطر لكل مهارة)</label>
        <textarea id="skillsText" class="admin-control" rows="4" wire:model="skillsText" placeholder="تخطيط المشاريع&#10;إدارة المخاطر"></textarea>
    </div>
</div>

<div class="admin-form-section">
    <h3 class="admin-form-section__title">المرفقات</h3>
    <p class="admin-crud-card__meta mb-3">أضف ملفات أو روابط مرجعية للبرنامج (دليل، خطة مقررات، إلخ).</p>
    @foreach ($attachmentRows as $index => $row)
        <div class="admin-filter-grid admin-attachment-row" style="grid-template-columns: 1fr 1fr auto; align-items: end;">
            <div class="admin-field">
                <label>اسم المرفق</label>
                <input type="text" class="admin-control" wire:model="attachmentRows.{{ $index }}.name">
            </div>
            <div class="admin-field">
                <label>الرابط (اختياري)</label>
                <input type="text" class="admin-control" wire:model="attachmentRows.{{ $index }}.url" dir="ltr">
            </div>
            <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="removeAttachment({{ $index }})">حذف</button>
        </div>
    @endforeach
    <button type="button" class="admin-btn-secondary admin-btn-secondary--sm mt-2" wire:click="addAttachment">+ إضافة مرفق</button>
</div>

@once
    @push('styles')
    <style>
        .admin-form-section { margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--sa-border); }
        .admin-form-section:last-child { border-bottom: 0; }
        .admin-form-section__title { font-size: 0.95rem; font-weight: 700; margin: 0 0 1rem; color: var(--sa-ink); }
        .admin-field--wide { grid-column: 1 / -1; }
        .admin-attachment-row { margin-bottom: 0.75rem; }
        .admin-status-badge { display: inline-block; padding: 0.2rem 0.55rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
        .admin-status-badge--active { background: var(--sa-green-soft); color: var(--sa-green-dark); }
        .admin-status-badge--inactive { background: var(--surface-track); color: var(--sa-muted); }
        .admin-status-badge--draft { background: #fff7ed; color: #c2410c; }
    </style>
    @endpush
@endonce
