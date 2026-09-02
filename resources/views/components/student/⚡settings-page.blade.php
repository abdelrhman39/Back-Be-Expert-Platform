<?php

use App\Services\MicrosoftTeams\TeamsOAuthService;
use App\Support\NotificationPreferences;
use App\Support\NotificationTypes;
use App\Support\PhoneNormalizer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app-user')]
#[Title('إعدادات الحساب | مركز التعلم المستمر')]
class extends Component
{
    public string $nameAr = '';

    public string $email = '';

    public string $phone = '';

    public string $currentPassword = '';

    public string $newPassword = '';

    public string $newPasswordConfirmation = '';

    /** @var array<string, array{mail: bool}> */
    public array $notificationPrefs = [];

    public function mount(): void
    {
        $user = auth()->user();
        $this->nameAr = $user?->name_ar ?? $user?->name ?? '';
        $this->email = $user?->email ?? '';
        $this->phone = $user?->phone ? ltrim(str_replace('+966', '0', $user->phone), '0') : '';
        if ($this->phone !== '' && ! str_starts_with($this->phone, '0')) {
            $this->phone = '0'.$this->phone;
        }

        if ($user) {
            $this->notificationPrefs = NotificationPreferences::forUser($user);
        }
    }

    public function saveProfile(): void
    {
        $user = auth()->user();

        $validated = $this->validate([
            'nameAr' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'phone' => ['required', 'string', 'min:9'],
        ], [], [
            'nameAr' => 'الاسم',
            'email' => 'البريد',
            'phone' => 'الجوال',
        ]);

        $user?->update([
            'name_ar' => $validated['nameAr'],
            'name' => $validated['nameAr'],
            'email' => $validated['email'],
            'phone' => PhoneNormalizer::toE164($validated['phone']),
        ]);

        session()->flash('portal_message', 'تم حفظ بيانات الحساب.');
    }

    public function changePassword(): void
    {
        $user = auth()->user();

        $this->validate([
            'currentPassword' => ['required', 'string'],
            'newPassword' => ['required', 'string', 'min:8', 'same:newPasswordConfirmation'],
        ], [], [
            'currentPassword' => 'كلمة السر الحالية',
            'newPassword' => 'كلمة السر الجديدة',
        ]);

        if (! Hash::check($this->currentPassword, $user?->password ?? '')) {
            $this->addError('currentPassword', 'كلمة السر الحالية غير صحيحة.');

            return;
        }

        $user?->update(['password' => $this->newPassword]);

        $this->reset(['currentPassword', 'newPassword', 'newPasswordConfirmation']);
        session()->flash('portal_message', 'تم تغيير كلمة السر بنجاح.');
    }

    public function disconnectTeams(TeamsOAuthService $oauth): void
    {
        $user = auth()->user();

        if ($user) {
            $oauth->disconnect($user);
        }

        session()->flash('portal_message', 'تم إلغاء ربط Microsoft Teams.');
    }

    public function saveNotificationPrefs(): void
    {
        $user = auth()->user();

        if ($user) {
            NotificationPreferences::save($user, $this->notificationPrefs);
        }

        session()->flash('portal_message', 'تم حفظ تفضيلات الإشعارات.');
    }
};
?>

@include('partials.portal.shell-start', ['portalActive' => 'settings', 'portalTitle' => 'الإعدادات'])

<div class="portal-dashboard portal-settings-page">
    @if (session('portal_message'))
        <div class="portal-alert portal-alert--success portal-alert--compact">
            <span class="portal-alert__icon"><i class="fa-solid fa-circle-check"></i></span>
            <div class="portal-alert__content">{{ session('portal_message') }}</div>
        </div>
    @endif

    <div class="portal-orders-intro">
        <div class="portal-orders-intro__text">
            <h1 class="portal-orders-intro__title">إعدادات الحساب</h1>
            <p class="portal-orders-intro__desc">حدّث بياناتك الشخصية وكلمة المرور.</p>
        </div>
    </div>

    <div class="portal-settings-grid">
        <section class="portal-panel portal-settings-form">
            <div class="portal-panel__head">
                <h2 class="portal-panel__title"><i class="fa-solid fa-user"></i> البيانات الشخصية</h2>
            </div>
            <div class="portal-panel__body portal-panel__body--padded">
                <form wire:submit="saveProfile">
                    <div class="mb-3">
                        <label class="form-label">الاسم</label>
                        <input type="text" class="form-control @error('nameAr') is-invalid @enderror" wire:model="nameAr">
                        @error('nameAr')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">البريد الإلكتروني</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" wire:model="email" dir="ltr">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">رقم الجوال</label>
                        <input type="tel" class="form-control @error('phone') is-invalid @enderror" wire:model="phone" dir="ltr" placeholder="05xxxxxxxx">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    @if (auth()->user()?->national_id)
                        <div class="mb-3">
                            <label class="form-label">رقم الهوية</label>
                            <input type="text" class="form-control bg-light" value="{{ auth()->user()->national_id }}" disabled dir="ltr">
                            <div class="form-text">لا يمكن تعديل رقم الهوية من هنا.</div>
                        </div>
                    @endif
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="saveProfile">حفظ التغييرات</span>
                        <span wire:loading wire:target="saveProfile">جاري الحفظ…</span>
                    </button>
                </form>
            </div>
        </section>

        <section class="portal-panel portal-settings-form">
            <div class="portal-panel__head">
                <h2 class="portal-panel__title"><i class="fa-solid fa-lock"></i> تغيير كلمة السر</h2>
            </div>
            <div class="portal-panel__body portal-panel__body--padded">
                <form wire:submit="changePassword">
                    <div class="mb-3">
                        <label class="form-label">كلمة السر الحالية</label>
                        <input type="password" class="form-control @error('currentPassword') is-invalid @enderror" wire:model="currentPassword">
                        @error('currentPassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">كلمة السر الجديدة</label>
                        <input type="password" class="form-control @error('newPassword') is-invalid @enderror" wire:model="newPassword">
                        @error('newPassword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">تأكيد كلمة السر</label>
                        <input type="password" class="form-control" wire:model="newPasswordConfirmation">
                    </div>
                    <button type="submit" class="btn btn-outline-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="changePassword">تحديث كلمة السر</span>
                        <span wire:loading wire:target="changePassword">جاري التحديث…</span>
                    </button>
                </form>
            </div>
        </section>

        @include('partials.portal.teams-connection', ['showDisconnect' => true])

        <section class="portal-panel portal-settings-form">
            <div class="portal-panel__head">
                <h2 class="portal-panel__title"><i class="fa-solid fa-bell"></i> تفضيلات الإشعارات</h2>
            </div>
            <div class="portal-panel__body portal-panel__body--padded">
                <p class="form-text mb-3">إشعارات المنصة داخل الموقع لا يمكن إيقافها. يمكنك إيقاف البريد الإلكتروني للإشعارات غير الحرجة فقط.</p>
                <form wire:submit="saveNotificationPrefs">
                    @foreach (NotificationPreferences::OPT_OUT_TYPES as $type)
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="pref-{{ $type }}"
                                wire:model="notificationPrefs.{{ $type }}.mail">
                            <label class="form-check-label" for="pref-{{ $type }}">
                                {{ NotificationTypes::labels()[$type] ?? $type }} — بريد إلكتروني
                            </label>
                        </div>
                    @endforeach
                    <div class="form-text mb-3">إشعار «المحاضرة جارية الآن» حرج ولا يُعطَّل.</div>
                    <button type="submit" class="btn btn-outline-primary btn-sm" wire:loading.attr="disabled">حفظ التفضيلات</button>
                </form>
            </div>
        </section>
    </div>
</div>

@include('partials.portal.shell-end')
