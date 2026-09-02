<?php

use App\Models\InstallmentContract;
use App\Services\InstallmentContractService;
use App\Support\InstallmentOptions;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-user')]
#[Title('تفاصيل التقسيط | مركز التعلم المستمر')]
class extends Component
{
    public InstallmentContract $contract;

    public string $signerName = '';

    public string $signatureData = '';

    public ?string $signMessage = null;

    public function mount(InstallmentContract $contract, InstallmentContractService $service): void
    {
        abort_unless($contract->user_id === auth()->id(), 403);
        $service->refreshOverdueStatuses();
        $this->contract = $contract->load(['schedules', 'template', 'student', 'program']);
        $this->signerName = auth()->user()->displayName();
    }

    public function signContract(InstallmentContractService $service): void
    {
        $this->validate([
            'signerName' => ['required', 'string', 'max:255'],
            'signatureData' => ['required', 'string'],
        ], [], ['signerName' => 'الاسم', 'signatureData' => 'التوقيع']);

        $this->contract = $service->signByStudent(
            $this->contract,
            auth()->user(),
            $this->signatureData,
            $this->signerName,
            request()->ip(),
        )->load(['schedules', 'template']);

        $this->reset('signatureData');

        $first = $this->contract->schedules->firstWhere('sequence', 1)
            ?? $this->contract->schedules->sortBy('sequence')->first();

        if ($first && $first->isPayable()) {
            $locale = app()->getLocale();

            $this->redirect(route('installments.pay', [
                'locale' => $locale,
                'contract' => $this->contract->id,
                'schedule' => $first->id,
            ]), navigate: true);

            return;
        }

        $this->signMessage = 'تم التوقيع الإلكتروني على العقد بنجاح. يمكنك الآن سداد الأقساط.';
    }
};
?>

@php $locale = app()->getLocale(); @endphp

@include('partials.portal.shell-start', ['portalActive' => 'installments', 'portalTitle' => 'تفاصيل التقسيط'])

<div class="portal-dashboard portal-installments-page">
    <div class="portal-orders-intro">
        <div class="portal-orders-intro__text">
            <h1 class="portal-orders-intro__title">{{ $contract->title }}</h1>
            <p class="portal-orders-intro__desc">{{ $contract->contract_no }} — {{ $contract->template?->name_ar }}</p>
        </div>
        <a href="{{ route('installments', ['locale' => $locale]) }}" class="portal-btn portal-btn--secondary portal-btn--sm">العودة</a>
    </div>

    @if ($contract->status === 'suspended')
        <div class="portal-alert portal-alert--warn portal-alert--compact">
            <span class="portal-alert__icon"><i class="fa-solid fa-ban"></i></span>
            <div class="portal-alert__content">{{ $contract->suspension_reason ?? 'العقد موقوف بسبب متأخرات.' }}</div>
        </div>
    @endif

    @if ($signMessage)
        <div class="portal-alert portal-alert--success portal-alert--compact">
            <span class="portal-alert__icon"><i class="fa-solid fa-check"></i></span>
            <div class="portal-alert__content">{{ $signMessage }}</div>
        </div>
    @endif

    @if ($contract->needsStudentSignature())
        <section class="portal-inst-panel portal-inst-sign-panel">
            <header class="portal-inst-panel__head">
                <h2>التوقيع الإلكتروني على العقد</h2>
                <p>يجب الموافقة والتوقيع قبل سداد أي قسط. بمبلغ إجمالي {{ number_format($contract->total_amount, 2) }} ر.س على {{ $contract->schedules->count() }} دفعات.</p>
            </header>

            <div class="portal-inst-contract-terms">
                <p>أقر أنا <strong>{{ $signerName }}</strong> بالالتزام بسداد جميع الأقساط في مواعيدها وفق جدول العقد <strong>{{ $contract->contract_no }}</strong>.</p>
            </div>

            <div class="portal-field">
                <label>الاسم الكامل للتوقيع</label>
                <input type="text" class="portal-control" wire:model="signerName">
                @error('signerName')<span class="portal-field-error">{{ $message }}</span>@enderror
            </div>

            <div class="portal-inst-signature-pad-wrap">
                <label>ارسم توقيعك في المربع أدناه</label>
                <canvas id="signature-pad" class="portal-inst-signature-pad" width="500" height="160"></canvas>
                <div class="portal-inst-hub-actions" style="margin-top:0.5rem;">
                    <button type="button" class="portal-btn portal-btn--ghost portal-btn--sm" id="signature-clear">مسح</button>
                    <button type="button" class="portal-btn portal-btn--primary portal-btn--sm" id="signature-submit">اعتماد التوقيع</button>
                </div>
                @error('signatureData')<span class="portal-field-error">{{ $message }}</span>@enderror
            </div>
        </section>
    @elseif ($contract->isStudentSigned())
        <div class="portal-alert portal-alert--info portal-alert--compact">
            <span class="portal-alert__icon"><i class="fa-solid fa-file-signature"></i></span>
            <div class="portal-alert__content">
                وقّع الطالب على العقد في {{ $contract->student_signed_at?->format('Y-m-d H:i') }}
                @if ($contract->student_signature_name) — {{ $contract->student_signature_name }} @endif
            </div>
        </div>
    @endif

    <div class="portal-inst-kpis portal-inst-kpis--compact">
        <div class="portal-inst-kpi"><span class="portal-inst-kpi__value">{{ number_format($contract->total_amount, 2) }}</span><span class="portal-inst-kpi__label">الإجمالي</span></div>
        <div class="portal-inst-kpi portal-inst-kpi--success"><span class="portal-inst-kpi__value">{{ number_format($contract->paid_amount, 2) }}</span><span class="portal-inst-kpi__label">المسدّد</span></div>
        <div class="portal-inst-kpi portal-inst-kpi--warn"><span class="portal-inst-kpi__value">{{ number_format($contract->remaining_balance, 2) }}</span><span class="portal-inst-kpi__label">المتبقي</span></div>
        <div class="portal-inst-kpi"><span class="portal-inst-kpi__value">{{ $contract->progressPercent() }}%</span><span class="portal-inst-kpi__label">نسبة الإنجاز</span></div>
    </div>

    <div class="portal-inst-progress portal-inst-progress--lg">
        <div class="portal-inst-progress__bar" style="width:{{ $contract->progressPercent() }}%"></div>
    </div>

    <section class="portal-inst-panel">
        <header class="portal-inst-panel__head">
            <h2>جدول الأقساط</h2>
            <p>جميع الدفعات — المسدّد والمتبقي.</p>
        </header>
        <div class="portal-inst-table-wrap">
            <table class="portal-inst-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>القسط</th>
                        <th>المبلغ</th>
                        <th>الاستحقاق</th>
                        <th>الحالة</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($contract->schedules as $schedule)
                        @php $display = $schedule->displayStatus(); @endphp
                        <tr wire:key="s-{{ $schedule->id }}">
                            <td>{{ $schedule->sequence }}</td>
                            <td>{{ $schedule->label }}</td>
                            <td>{{ number_format($schedule->amount, 2) }} ر.س</td>
                            <td dir="ltr">{{ $schedule->due_date->format('Y-m-d') }}</td>
                            <td><span class="portal-inst-badge {{ InstallmentOptions::scheduleBadgeClass($display) }}">{{ InstallmentOptions::scheduleStatusLabel($display) }}</span></td>
                            <td>
                                @if ($schedule->isPayable() && $contract->isStudentSigned() && $contract->status !== 'suspended')
                                    <a href="{{ route('installments.pay', ['locale' => $locale, 'contract' => $contract->id, 'schedule' => $schedule->id]) }}" class="portal-btn portal-btn--primary portal-btn--sm">سداد الآن</a>
                                @elseif ($schedule->status === 'paid')
                                    <span class="portal-inst-empty">{{ $schedule->paid_at?->format('Y-m-d') }}</span>
                                @elseif ($contract->needsStudentSignature())
                                    <span class="portal-inst-empty">يتطلب التوقيع</span>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>

@script
<script>
    (function initSignaturePad() {
        const canvas = document.getElementById('signature-pad');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        let drawing = false;

        const resize = () => {
            const ratio = window.devicePixelRatio || 1;
            const rect = canvas.getBoundingClientRect();
            canvas.width = rect.width * ratio;
            canvas.height = rect.height * ratio;
            ctx.scale(ratio, ratio);
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.strokeStyle = '#0f172a';
        };
        resize();

        const pos = (e) => {
            const rect = canvas.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return { x: clientX - rect.left, y: clientY - rect.top };
        };

        const start = (e) => { drawing = true; const p = pos(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); e.preventDefault(); };
        const move = (e) => { if (!drawing) return; const p = pos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); e.preventDefault(); };
        const end = () => { drawing = false; };

        canvas.addEventListener('mousedown', start);
        canvas.addEventListener('mousemove', move);
        canvas.addEventListener('mouseup', end);
        canvas.addEventListener('mouseleave', end);
        canvas.addEventListener('touchstart', start, { passive: false });
        canvas.addEventListener('touchmove', move, { passive: false });
        canvas.addEventListener('touchend', end);

        document.getElementById('signature-clear')?.addEventListener('click', () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        });

        document.getElementById('signature-submit')?.addEventListener('click', () => {
            $wire.set('signatureData', canvas.toDataURL('image/png'));
            $wire.call('signContract');
        });
    })();
</script>
@endscript

@include('partials.portal.shell-end')
