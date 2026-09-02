<?php

use App\Services\InstructorService;
use App\Services\MicrosoftTeams\TeamsOAuthService;
use App\Support\InstructorPermissions;
use App\Support\TeamsSettings;
use App\Support\ZoomSettings;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-user')]
#[Title('إعدادات المدرب | مركز التعلم المستمر')]
class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';

    public function mount(InstructorService $instructors): void
    {
        $instructors->authorizePermission(auth()->user(), 'instructor.profile.update');
        $user = auth()->user();
        $this->name = $user->name_ar ?: $user->name;
        $this->email = (string) $user->email;
        $this->phone = (string) ($user->phone ?: '');
    }

    public function saveProfile(): void
    {
        app(InstructorService::class)->authorizePermission(auth()->user(), 'instructor.profile.update');

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
        ], [], [
            'name' => 'الاسم',
            'phone' => 'الجوال',
        ]);

        $user = auth()->user();
        $user->update([
            'name_ar' => $validated['name'],
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?: null,
        ]);

        if ($user->academicStaff) {
            $user->academicStaff->update(['name_ar' => $validated['name']]);
        }

        session()->flash('instructor_message', 'تم تحديث بيانات الملف الشخصي.');
    }

    public function disconnectTeams(TeamsOAuthService $oauth): void
    {
        $user = auth()->user();

        if ($user) {
            $oauth->disconnect($user);
        }

        session()->flash('instructor_message', 'تم إلغاء ربط Microsoft Teams.');
    }
};
?>

@php
    $locale = app()->getLocale();
    $user = auth()->user();
    $staff = $user->academicStaff;
    $preset = $staff?->permission_preset ?: 'instructor.lead';
@endphp

@include('partials.instructor.shell-start', ['instructorActive' => 'settings', 'instructorTitle' => 'الإعدادات'])

<div class="portal-dashboard portal-dashboard--instructor">
    @include('partials.instructor.page-hero', [
        'title' => 'إعدادات المدرب',
        'desc' => 'إدارة ملفك الشخصي وروابط الاجتماعات (Teams / Zoom).',
        'icon' => 'fa-gear',
        'stats' => [
            ['value' => $staff?->status === 'active' ? 'نشط' : '—', 'label' => 'الحساب'],
            ['value' => ZoomSettings::enabled() ? 'جاهز' : 'مغلق', 'label' => 'Zoom'],
            ['value' => TeamsSettings::isEnabled() && TeamsSettings::isConfigured() ? 'جاهز' : 'مغلق', 'label' => 'Teams'],
        ],
        'actions' => [
            ['href' => route('instructor.dashboard', ['locale' => $locale]), 'label' => 'العودة للوحة', 'icon' => 'fa-arrow-right', 'class' => 'btn-light border'],
        ],
    ])

    @if (session('instructor_message'))
        <div class="portal-alert portal-alert--success portal-alert--compact">
            <span class="portal-alert__icon"><i class="fa-solid fa-circle-check"></i></span>
            <div class="portal-alert__content">{{ session('instructor_message') }}</div>
        </div>
    @endif

    <div class="portal-dashboard-grid portal-dashboard-grid--wide">
        <div class="portal-main-col">
            <section class="portal-panel">
                <div class="portal-panel__head">
                    <h2 class="portal-panel__title"><i class="fa-solid fa-id-card"></i> الملف الشخصي</h2>
                </div>
                <div class="portal-panel__body portal-panel__body--padded">
                    <form wire:submit="saveProfile" class="portal-inst-settings-form">
                        <label>
                            <span>الاسم</span>
                            <input type="text" wire:model="name">
                            @error('name')<small>{{ $message }}</small>@enderror
                        </label>
                        <label>
                            <span>البريد الإلكتروني</span>
                            <input type="email" value="{{ $email }}" disabled dir="ltr">
                            <small class="is-muted">لتغيير البريد تواصل مع الإدارة.</small>
                        </label>
                        <label>
                            <span>الجوال</span>
                            <input type="tel" wire:model="phone" dir="ltr">
                            @error('phone')<small>{{ $message }}</small>@enderror
                        </label>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <i class="fa-solid fa-floppy-disk"></i> حفظ الملف
                        </button>
                    </form>
                </div>
            </section>

            <section class="portal-panel">
                <div class="portal-panel__head">
                    <h2 class="portal-panel__title"><i class="fa-brands fa-microsoft"></i> Microsoft Teams</h2>
                </div>
                <div class="portal-panel__body portal-panel__body--padded">
                    @if (TeamsSettings::isEnabled() && TeamsSettings::isConfigured())
                        @include('partials.portal.teams-connection', ['showDisconnect' => true])
                    @else
                        <div class="portal-alert portal-alert--warn portal-alert--compact">
                            <div class="portal-alert__content">تكامل Teams غير مفعّل بعد من الإدارة.</div>
                        </div>
                    @endif
                </div>
            </section>
        </div>

        <aside class="portal-side-col">
            <div class="portal-widget portal-widget--academic">
                <div class="portal-widget__head">
                    <span class="portal-widget__head-icon"><i class="fa-solid fa-shield-halved"></i></span>
                    <h3 class="portal-widget__title">حساب المنصة</h3>
                </div>
                <div class="portal-academic-list">
                    <div class="portal-academic-item">
                        <span class="portal-academic-item__label">الدور</span>
                        <strong>{{ $staff?->role ?: 'instructor' }}</strong>
                    </div>
                    <div class="portal-academic-item">
                        <span class="portal-academic-item__label">حزمة الصلاحيات</span>
                        <strong>{{ InstructorPermissions::presetLabels()[$preset] ?? $preset }}</strong>
                    </div>
                    <div class="portal-academic-item">
                        <span class="portal-academic-item__label">التخصص</span>
                        <strong>{{ $staff?->specialty ?: '—' }}</strong>
                    </div>
                    <div class="portal-academic-item">
                        <span class="portal-academic-item__label">حالة الحساب</span>
                        <strong>{{ $staff?->status === 'active' ? 'نشط' : ($staff?->status ?: '—') }}</strong>
                    </div>
                    <div class="portal-academic-item">
                        <span class="portal-academic-item__label">صلاحيات الحزمة</span>
                        <strong>{{ count(InstructorPermissions::presetPermissions($preset)) }}</strong>
                    </div>
                </div>
            </div>

            <section class="portal-panel">
                <div class="portal-panel__head">
                    <h2 class="portal-panel__title"><i class="fa-solid fa-video"></i> Zoom</h2>
                </div>
                <div class="portal-panel__body portal-panel__body--padded">
                    @if (ZoomSettings::enabled())
                        <div class="portal-alert portal-alert--success portal-alert--compact">
                            <div class="portal-alert__content">Zoom مفعّل وجاهز. يمكنك إنشاء/بدء الاجتماع من صفحة الحصة.</div>
                        </div>
                    @else
                        <div class="portal-alert portal-alert--warn portal-alert--compact">
                            <div class="portal-alert__content">Zoom غير مفعّل أو غير مكتمل الإعداد من لوحة الإدارة.</div>
                        </div>
                    @endif
                </div>
            </section>
        </aside>
    </div>
</div>

@push('styles')
<style>
.portal-hero--page .portal-hero__banner--compact{min-height:auto}
.portal-hero--page .portal-hero__eyebrow{display:inline-flex;align-items:center;gap:.4rem;font-size:.72rem;font-weight:800;color:rgba(255,255,255,.78);margin-bottom:.35rem}
.portal-hero--page .portal-hero__body--compact{margin-top:-1.1rem;padding-top:0;padding-bottom:1rem}
.portal-hero--page .portal-hero__actions--start{justify-content:flex-start}
.portal-inst-settings-form{display:grid;gap:.85rem;max-width:34rem}
.portal-inst-settings-form label{display:grid;gap:.35rem}
.portal-inst-settings-form span{font-size:.72rem;font-weight:800;color:#64748b}
.portal-inst-settings-form input{width:100%;border:1px solid #dbe4ee;border-radius:12px;padding:.8rem .9rem;background:#fff}
.portal-inst-settings-form input:disabled{background:#f8fafc;color:#64748b}
.portal-inst-settings-form small{color:#b91c1c;font-size:.7rem;font-weight:700}
.portal-inst-settings-form small.is-muted{color:#94a3b8;font-weight:600}
</style>
@endpush

@include('partials.instructor.shell-end')
