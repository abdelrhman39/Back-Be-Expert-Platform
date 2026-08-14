@php
    use App\Support\AcademicCourseOptions;
@endphp

<div class="admin-form-section">
    <h3 class="admin-form-section__title">البيانات الأساسية</h3>
    <div class="admin-filter-grid" style="grid-template-columns: repeat(2, 1fr);">
        <div class="admin-field">
            <label for="nameAr">اسم المقرر (عربي) *</label>
            <input id="nameAr" type="text" class="admin-control" wire:model="nameAr">
            @error('nameAr')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
        </div>
        <div class="admin-field">
            <label for="nameEn">اسم المقرر (إنجليزي)</label>
            <input id="nameEn" type="text" class="admin-control" wire:model="nameEn">
        </div>
        <div class="admin-field">
            <label for="symbolAr">رمز المقرر (عربي)</label>
            <input id="symbolAr" type="text" class="admin-control" wire:model="symbolAr">
        </div>
        <div class="admin-field">
            <label for="symbolEn">رمز المقرر (إنجليزي)</label>
            <input id="symbolEn" type="text" class="admin-control" wire:model="symbolEn" dir="ltr">
        </div>
        <div class="admin-field">
            <label for="code">كود المقرر *</label>
            <input id="code" type="text" class="admin-control" wire:model="code" dir="ltr">
            @error('code')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
        </div>
        <div class="admin-field">
            <label for="creditHours">عدد الساعات</label>
            <input id="creditHours" type="number" min="0" max="30" class="admin-control" wire:model="creditHours">
        </div>
    </div>
</div>

<div class="admin-form-section">
    <h3 class="admin-form-section__title">الربط الأكاديمي</h3>
    <div class="admin-filter-grid" style="grid-template-columns: repeat(2, 1fr);">
        <div class="admin-field">
            <label for="programId">البرنامج *</label>
            <select id="programId" class="admin-control" wire:model.live="programId">
                <option value="">— اختر البرنامج —</option>
                @foreach ($programs as $prog)
                    <option value="{{ $prog->id }}">{{ $prog->name_ar }} ({{ $prog->code }})</option>
                @endforeach
            </select>
            @error('programId')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
        </div>
        <div class="admin-field">
            <label for="levelId">المستوى الدراسي</label>
            <select id="levelId" class="admin-control" wire:model="levelId" @disabled(! $programId)>
                <option value="">—</option>
                @foreach ($levels as $level)
                    <option value="{{ $level->id }}">{{ $level->name_ar }}</option>
                @endforeach
            </select>
        </div>
        <div class="admin-field">
            <label for="status">الحالة *</label>
            <select id="status" class="admin-control" wire:model="status">
                @foreach (AcademicCourseOptions::statuses() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="admin-field">
            <label for="targetGroup">الفئة المستهدفة</label>
            <input id="targetGroup" type="text" class="admin-control" wire:model="targetGroup" placeholder="مثال: الموظفون على رأس العمل">
        </div>
    </div>
</div>

<div class="admin-form-section">
    <h3 class="admin-form-section__title">صورة المقرر</h3>
    <p class="admin-crud-card__meta mb-3">اختر من مكتبة الوسائط، ارفع صورة، أو أدخل رابطاً مباشراً.</p>
    @if ($existingImageUrl && ! $imageFile && ! $imageUrl)
        <div class="admin-course-image admin-course-image--preview mb-3">
            <img src="{{ $existingImageUrl }}" alt="معاينة صورة المقرر">
        </div>
    @endif
    @if ($imageFile)
        <div class="admin-course-image admin-course-image--preview mb-3">
            <img src="{{ $imageFile->temporaryUrl() }}" alt="معاينة الصورة الجديدة">
        </div>
    @endif
    @include('partials.admin.media-field', [
        'wireModel' => 'imageUrl',
        'id' => 'imageUrl',
        'label' => 'صورة من المكتبة أو رابط',
        'previewUrl' => filled($imageUrl)
            ? (str_starts_with($imageUrl, 'http') ? $imageUrl : resolve_poster_url($imageUrl))
            : ($existingImageUrl && ! $imageFile ? $existingImageUrl : null),
        'placeholder' => '/storage/... أو https://...',
    ])
    <div class="admin-field" style="margin-top:0.75rem;">
        <label for="imageFile">أو رفع مباشر</label>
        <input id="imageFile" type="file" class="admin-control" wire:model="imageFile" accept="image/*">
        @error('imageFile')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
        <div wire:loading wire:target="imageFile" class="admin-field-hint">جاري تحميل الصورة...</div>
    </div>
    @if ($existingImageUrl || $imageFile || $imageUrl)
        <button type="button" class="admin-btn-secondary admin-btn-secondary--sm mt-2" wire:click="clearImage">إزالة الصورة</button>
    @endif
</div>

@once
    @push('styles')
    <style>
        .admin-course-image--preview {
            display: flex;
            justify-content: center;
            max-width: 280px;
            padding: 0.5rem;
            border: 1px solid var(--sa-border);
            border-radius: var(--radius-md);
            background: var(--sa-mist);
        }
        .admin-course-image--preview img { max-width: 100%; max-height: 160px; object-fit: contain; border-radius: var(--radius-sm); }
    </style>
    @endpush
@endonce

<div class="admin-form-section">
    <h3 class="admin-form-section__title">وصف إضافي</h3>
    <div class="admin-field">
        <label for="summary">ملاحظات / وصف المقرر</label>
        <textarea id="summary" class="admin-control" rows="4" wire:model="summary"></textarea>
    </div>
</div>
