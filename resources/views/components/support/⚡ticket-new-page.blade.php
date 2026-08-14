<?php

use App\Services\SupportTicketService;
use App\Support\SupportTicketOptions;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-inner')]
#[Title('إنشاء تذكرة | مركز التعلم المستمر')]
class extends Component
{
    public string $name = '';

    public string $email = '';

    public string $nationalId = '';

    public string $phone = '';

    public string $category = '';

    public string $specialization = '';

    public string $subject = '';

    public string $body = '';

    public ?string $createdReference = null;

    public function mount(): void
    {
        $user = auth()->user();

        if ($user) {
            $this->name = $user->displayName();
            $this->email = $user->email ?? '';
            $this->nationalId = $user->national_id ?? '';
            $this->phone = $user->phone ?? '';
        }
    }

    public function submit(SupportTicketService $service): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'nationalId' => ['nullable', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:20'],
            'category' => ['required', 'in:'.implode(',', array_keys(SupportTicketOptions::categories()))],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'min:20'],
        ], [], [
            'name' => 'الاسم',
            'email' => 'البريد الإلكتروني',
            'category' => 'الفئة',
            'subject' => 'الموضوع',
            'body' => 'الوصف',
        ]);

        $body = $validated['body'];

        if (filled($this->specialization)) {
            $body = "التخصص: {$this->specialization}\n\n".$body;
        }

        $ticket = $service->create([
            'subject' => $validated['subject'],
            'category' => $validated['category'],
            'contact_name' => $validated['name'],
            'contact_email' => $validated['email'],
            'contact_phone' => $validated['phone'] ?: null,
            'contact_national_id' => $validated['nationalId'] ?: null,
            'body' => $body,
        ], auth()->user());

        $service->grantGuestAccess($ticket);
        $this->createdReference = $ticket->reference_code;
    }
};
?>

<div class="support-page">
    @include('partials.support.nav', ['active' => 'new'])

    <div class="container support-page__container">
        @if ($createdReference)
            <div class="support-success">
                <div class="support-success__icon">✓</div>
                <h1>تم إرسال تذكرتك بنجاح</h1>
                <p>رقم التذكرة:</p>
                <code class="support-success__ref">{{ $createdReference }}</code>
                <p class="support-success__hint">احفظ الرقم — ستحتاجه مع بريدك لمتابعة التذكرة.</p>
                <div class="support-success__actions">
                    <a href="{{ route('support.ticket.view', ['locale' => app()->getLocale(), 'ticket' => $createdReference]) }}" class="btn btn-primary">متابعة التذكرة</a>
                    <a href="{{ route('support.ticket.search', ['locale' => app()->getLocale()]) }}" class="btn btn-outline-secondary">بحث لاحقاً</a>
                </div>
            </div>
        @else
            <div class="row g-4 align-items-start">
                <div class="col-lg-7 order-2 order-lg-1">
                    <h1 class="support-page__title">مرحباً</h1>
                    <p class="support-page__lead">برجاء إملاء البيانات المطلوبة لتسهيل حل المشكلة.</p>

                    <form wire:submit="submit" class="support-form-card">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">الاسم *</label>
                                <input type="text" class="form-control" wire:model="name">
                                @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">البريد الإلكتروني *</label>
                                <input type="email" class="form-control" wire:model="email" dir="ltr">
                                @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">رقم الهوية</label>
                                <input type="text" class="form-control" wire:model="nationalId" dir="ltr">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">رقم الجوال</label>
                                <input type="tel" class="form-control" wire:model="phone" dir="ltr">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">الفئة *</label>
                                <select class="form-select" wire:model="category">
                                    <option value="">اختر</option>
                                    @foreach (SupportTicketOptions::categories() as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('category') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">التخصص</label>
                                <input type="text" class="form-control" wire:model="specialization">
                            </div>
                            <div class="col-12">
                                <label class="form-label">الموضوع *</label>
                                <input type="text" class="form-control" wire:model="subject">
                                @error('subject') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">الوصف *</label>
                                <textarea class="form-control" wire:model="body" rows="7" placeholder="صف المشكلة أو الاستفسار بتفصيل..."></textarea>
                                @error('body') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100 mt-4" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="submit">إرسال التذكرة</span>
                            <span wire:loading wire:target="submit">جاري الإرسال…</span>
                        </button>
                    </form>
                </div>
                <div class="col-lg-5 order-1 order-lg-2">
                    <aside class="support-aside">
                        <div class="support-aside__icon">💻</div>
                        <h2>إنشاء تذكرة</h2>
                        <p>تتيح لك هذه الخدمة إنشاء تذكرة دعم فني بسهولة للتواصل مع فريق الدعم وحل المشكلات التقنية أو الاستفسارات المتعلقة بالمنصة.</p>
                    </aside>
                </div>
            </div>
        @endif
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('css/support-pages.css') }}">
@endpush
