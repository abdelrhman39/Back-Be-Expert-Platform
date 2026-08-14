@php
    use App\Support\AcademicBatchOptions;
@endphp

<div class="admin-form-section">
    <h3 class="admin-form-section__title">البيانات الأساسية</h3>
    <div class="admin-filter-grid" style="grid-template-columns: repeat(2, 1fr);">
        <div class="admin-field">
            <label for="name">اسم الدفعة *</label>
            <input id="name" type="text" class="admin-control" wire:model="name">
            @error('name')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
        </div>
        <div class="admin-field">
            <label for="code">كود الدفعة *</label>
            <input id="code" type="text" class="admin-control" wire:model="code" dir="ltr" inputmode="numeric">
            @error('code')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
        </div>
        <div class="admin-field">
            <label for="programId">البرنامج *</label>
            <select id="programId" class="admin-control" wire:model="programId">
                <option value="">— اختر البرنامج —</option>
                @foreach ($programs as $prog)
                    <option value="{{ $prog->id }}">{{ $prog->name_ar }} ({{ $prog->code }})</option>
                @endforeach
            </select>
            @error('programId')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
        </div>
        <div class="admin-field">
            <label for="status">الحالة *</label>
            <select id="status" class="admin-control" wire:model="status">
                @foreach (AcademicBatchOptions::statuses() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="admin-form-section">
    <h3 class="admin-form-section__title">الفصل والجدولة</h3>
    <div class="admin-filter-grid" style="grid-template-columns: repeat(2, 1fr);">
        <div class="admin-field">
            <label for="semesterKey">فصل القبول</label>
            <select id="semesterKey" class="admin-control" wire:model.live="semesterKey">
                <option value="">—</option>
                @foreach (AcademicBatchOptions::semesters() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="admin-field">
            <label for="semester">وصف الفصل (نص حر)</label>
            <input id="semester" type="text" class="admin-control" wire:model="semester" placeholder="يُملأ تلقائياً عند اختيار الفصل">
        </div>
        <div class="admin-field">
            <label for="startDate">تاريخ البدء</label>
            <input id="startDate" type="date" class="admin-control" wire:model="startDate">
        </div>
        <div class="admin-field">
            <label for="endDate">تاريخ الانتهاء</label>
            <input id="endDate" type="date" class="admin-control" wire:model="endDate">
        </div>
        <div class="admin-field">
            <label for="studyMode">نمط الدراسة</label>
            <select id="studyMode" class="admin-control" wire:model="studyMode">
                <option value="">—</option>
                @foreach (AcademicBatchOptions::studyModes() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="admin-field">
            <label for="coordinator">منسق الدفعة</label>
            <input id="coordinator" type="text" class="admin-control" wire:model="coordinator">
        </div>
    </div>
</div>

<div class="admin-form-section">
    <h3 class="admin-form-section__title">السعة والتسجيل</h3>
    <div class="admin-filter-grid" style="grid-template-columns: repeat(3, 1fr);">
        <div class="admin-field">
            <label for="capacity">السعة القصوى</label>
            <input id="capacity" type="number" min="0" class="admin-control" wire:model="capacity">
        </div>
        <div class="admin-field">
            <label for="tuitionAmount">رسوم البرنامج (ر.س)</label>
            <input id="tuitionAmount" type="number" min="0" step="0.01" class="admin-control" wire:model="tuitionAmount" placeholder="مثال: 12000">
            @error('tuitionAmount')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
        </div>
        <div class="admin-field" style="display:flex;align-items:flex-end;padding-bottom:0.5rem;">
            <label class="admin-checkbox">
                <input type="checkbox" wire:model="enrollmentOpen">
                <span>التسجيل مفتوح</span>
            </label>
        </div>
        <div class="admin-field" style="display:flex;align-items:flex-end;padding-bottom:0.5rem;">
            <label class="admin-checkbox">
                <input type="checkbox" wire:model="installmentAllowed">
                <span>السماح بالتقسيط</span>
            </label>
        </div>
        <div class="admin-field admin-field--wide">
            <p class="admin-crud-card__meta">عدد الطلاب يُحدَّث تلقائياً من سجل الطلاب المسجلين في الدفعة.</p>
        </div>
    </div>
    <div class="admin-field admin-field--wide">
        <label for="notes">ملاحظات</label>
        <textarea id="notes" class="admin-control" rows="3" wire:model="notes"></textarea>
    </div>
</div>
