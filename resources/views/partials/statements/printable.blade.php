@php
    use App\Models\PlatformSetting;
    use App\Support\StatementOptions;

    $platformName = PlatformSetting::get('platform_name_ar', config('app.name')) ?? config('app.name');
    $payload = $statement->payload ?? [];
@endphp

<div class="portal-print-statement">
    <div class="portal-print-statement__frame">
        <div class="portal-print-statement__header">
            <strong>{{ $platformName }}</strong>
            <h2>{{ $statement->title }}</h2>
            <p>رقم المرجع: <span dir="ltr">{{ $statement->reference_no }}</span></p>
        </div>

        <div class="portal-print-statement__body">
            <p>إلى من يهمه الأمر،</p>
            <p>
                نفيد بأن الطالب/ـة <strong>{{ $payload['holder_name'] ?? $statement->user?->displayName() }}</strong>
                @if (! empty($payload['national_id']))
                    — هوية رقم <span dir="ltr">{{ $payload['national_id'] }}</span>
                @endif
                @if (! empty($payload['academic_id']))
                    — رقم أكاديمي <span dir="ltr">{{ $payload['academic_id'] }}</span>
                @endif
            </p>

            @if (! empty($payload['program_name']))
                <p>مسجّل/ـة في برنامج: <strong>{{ $payload['program_name'] }}</strong></p>
            @endif

            @if (! empty($payload['section_name']))
                <p>الشعبة: <strong>{{ $payload['section_name'] }}</strong></p>
            @endif

            @if (! empty($payload['study_status']))
                <p>الحالة الدراسية: <strong>{{ $payload['study_status'] }}</strong></p>
            @endif

            <p>نوع الإفادة: <strong>{{ StatementOptions::typeLabel($statement->type) }}</strong></p>

            @if ($statement->student_notes)
                <p>ملاحظات الطالب: {{ $statement->student_notes }}</p>
            @endif
        </div>

        <div class="portal-print-statement__footer">
            <div>
                <span>تاريخ الطلب</span>
                <strong>{{ $statement->requested_at?->translatedFormat('d M Y') }}</strong>
            </div>
            <div>
                <span>تاريخ الإصدار</span>
                <strong>{{ $statement->issued_at?->translatedFormat('d M Y') ?? '—' }}</strong>
            </div>
        </div>
    </div>
</div>

<style>
    .portal-print-statement { max-width: 760px; margin: 0 auto; }
    .portal-print-statement__frame { border: 1px solid #cbd5e1; padding: 2rem; background: #fff; }
    .portal-print-statement__header { text-align: center; border-bottom: 2px solid #165d31; padding-bottom: 1rem; margin-bottom: 1rem; }
    .portal-print-statement__header h2 { margin: 0.35rem 0; font-size: 1.2rem; }
    .portal-print-statement__body p { line-height: 1.8; margin-bottom: 0.75rem; }
    .portal-print-statement__footer { display: flex; justify-content: space-between; gap: 1rem; margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #e2e8f0; }
    .portal-print-statement__footer span { display: block; font-size: 0.75rem; color: #94a3b8; }
</style>
