@php use App\Support\AcademicBatchOptions; use App\Support\AcademicSectionOptions; @endphp

<div class="admin-form-section">
    <h3 class="admin-form-section__title">بيانات الشعبة</h3>
    <div class="admin-filter-grid" style="grid-template-columns:repeat(2,1fr);">
        <div class="admin-field">
            <label>اسم الشعبة *</label>
            <input type="text" class="admin-control" wire:model="name">
            @error('name')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
        </div>
        <div class="admin-field">
            <label>رمز الشعبة *</label>
            <input type="text" class="admin-control" wire:model="code" dir="ltr">
            @error('code')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
        </div>
        <div class="admin-field admin-field--wide">
            <label>وصف المقرر (عنوان فرعي)</label>
            <input type="text" class="admin-control" wire:model="subtitle">
        </div>
    </div>
</div>

<div class="admin-form-section">
    <h3 class="admin-form-section__title">الربط الأكاديمي</h3>
    <div class="admin-filter-grid" style="grid-template-columns:repeat(2,1fr);">
        <div class="admin-field">
            <label>البرنامج</label>
            <select class="admin-control" wire:model.live="programId">
                <option value="">—</option>
                @foreach ($programs as $prog)
                    <option value="{{ $prog->id }}">{{ $prog->name_ar }}</option>
                @endforeach
            </select>
        </div>
        <div class="admin-field">
            <label>الدفعة</label>
            <select class="admin-control" wire:model.live="batchId">
                <option value="">—</option>
                @foreach ($batches as $batch)
                    <option value="{{ $batch->id }}">{{ $batch->name }} ({{ $batch->code }})</option>
                @endforeach
            </select>
        </div>
        <div class="admin-field">
            <label>المقرر</label>
            <select class="admin-control" wire:model="courseId" @disabled(! $programId)>
                <option value="">—</option>
                @foreach ($courses as $course)
                    <option value="{{ $course->id }}">{{ $course->name_ar }}</option>
                @endforeach
            </select>
        </div>
        <div class="admin-field">
            <label>المستوى</label>
            <select class="admin-control" wire:model="levelId" @disabled(! $programId)>
                <option value="">—</option>
                @foreach ($levels as $level)
                    <option value="{{ $level->id }}">{{ $level->name_ar }}</option>
                @endforeach
            </select>
        </div>
        <div class="admin-field">
            <label>فصل القبول</label>
            <select class="admin-control" wire:model.live="semesterKey">
                <option value="">—</option>
                @foreach (AcademicBatchOptions::semesters() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="admin-field">
            <label>الفترة</label>
            <select class="admin-control" wire:model="period">
                <option value="">—</option>
                @foreach (AcademicSectionOptions::periods() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="admin-form-section">
    <h3 class="admin-form-section__title">الإشراف والسعة</h3>
    <div class="admin-filter-grid" style="grid-template-columns:repeat(3,1fr);">
        <div class="admin-field">
            <label>المشرف</label>
            <input type="text" class="admin-control" wire:model="supervisor">
        </div>
        <div class="admin-field">
            <label>السعة القصوى</label>
            <input type="number" min="1" class="admin-control" wire:model="maxCapacity">
        </div>
        <div class="admin-field">
            <label>عدد الطلاب</label>
            <input type="number" min="0" class="admin-control" wire:model="studentsCount">
        </div>
        <div class="admin-field">
            <label>الحالة</label>
            <select class="admin-control" wire:model="status">
                @foreach (AcademicSectionOptions::statuses() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
