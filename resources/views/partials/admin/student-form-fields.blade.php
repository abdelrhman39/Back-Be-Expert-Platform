@php use App\Support\AcademicStudentOptions; @endphp

<div class="admin-form-section">
    <h3 class="admin-form-section__title">البيانات الشخصية</h3>
    <div class="admin-filter-grid" style="grid-template-columns:repeat(2,1fr);">
        <div class="admin-field">
            <label>الاسم (عربي) *</label>
            <input type="text" class="admin-control" wire:model="nameAr">
            @error('nameAr')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
        </div>
        <div class="admin-field">
            <label>الاسم (إنجليزي)</label>
            <input type="text" class="admin-control" wire:model="nameEn" dir="ltr">
        </div>
        <div class="admin-field">
            <label>الرقم الأكاديمي *</label>
            <input type="text" class="admin-control" wire:model="academicId" dir="ltr">
            @error('academicId')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
        </div>
        <div class="admin-field">
            <label>رقم الهوية</label>
            <input type="text" class="admin-control" wire:model="nationalId" dir="ltr">
        </div>
        <div class="admin-field">
            <label>الجنس</label>
            <select class="admin-control" wire:model="gender">
                <option value="">—</option>
                @foreach (AcademicStudentOptions::genders() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="admin-field">
            <label>المدينة</label>
            <input type="text" class="admin-control" wire:model="city">
        </div>
    </div>
</div>

<div class="admin-form-section">
    <h3 class="admin-form-section__title">التواصل والدفعة</h3>
    <div class="admin-filter-grid" style="grid-template-columns:repeat(2,1fr);">
        <div class="admin-field">
            <label>الجوال</label>
            <input type="text" class="admin-control" wire:model="mobile" dir="ltr">
        </div>
        <div class="admin-field">
            <label>البريد الإلكتروني</label>
            <input type="email" class="admin-control" wire:model="email" dir="ltr">
            @error('email')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
        </div>
        <div class="admin-field">
            <label>الدفعة الدراسية</label>
            <select class="admin-control" wire:model.live="batchId">
                <option value="">—</option>
                @foreach ($batches as $batch)
                    <option value="{{ $batch->id }}">{{ $batch->name }} ({{ $batch->code }})</option>
                @endforeach
            </select>
        </div>
        <div class="admin-field">
            <label>الشعبة الدراسية</label>
            <select class="admin-control" wire:model="sectionId" @disabled(! $batchId)>
                <option value="">— {{ $batchId ? 'بدون شعبة' : 'اختر الدفعة أولاً' }} —</option>
                @foreach ($sections as $section)
                    <option value="{{ $section->id }}">
                        {{ $section->name }} ({{ $section->code }})
                        — {{ $section->students_count }}/{{ $section->max_capacity }}
                    </option>
                @endforeach
            </select>
            @error('sectionId')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
            @if ($batchId && $sections->isEmpty())
                <div class="admin-field-hint">لا توجد شعب لهذه الدفعة. <a href="{{ route('admin.sections.create', ['batch' => $batchId]) }}" class="admin-link">إنشاء شعبة</a></div>
            @endif
        </div>
        <div class="admin-field">
            <label>الحالة الأكاديمية *</label>
            <select class="admin-control" wire:model.live="academicStatus">
                @foreach (AcademicStudentOptions::academicStatuses() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        @if (! empty($requiresStatusReason))
            <div class="admin-field admin-field--wide">
                <label>سبب تغيير الحالة بعد تأكيد السداد *</label>
                <textarea class="admin-control" rows="3" wire:model="statusChangeReason" placeholder="مثال: طلب انسحاب من الطالب، تأجيل فصل، إيقاف مؤقت بسبب الأقساط..."></textarea>
                <div class="admin-field-hint is-visible">هذا الطالب مؤكد السداد. التغيير يُسجَّل في التدقيق وCRM ولا يُرجع مرحلة السداد تلقائياً.</div>
                @error('statusChangeReason')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
            </div>
        @endif
        <div class="admin-field admin-field--wide">
            <label>حالة الدراسة (نص للعرض)</label>
            <input type="text" class="admin-control" wire:model="studyStatus" placeholder="يُملأ تلقائياً من الحالة الأكاديمية">
        </div>
        <div class="admin-field" style="display:flex;align-items:flex-end;padding-bottom:0.5rem;">
            <label class="admin-checkbox">
                <input type="checkbox" wire:model="loginAllowed">
                <span>السماح بتسجيل الدخول</span>
            </label>
        </div>
    </div>
</div>
