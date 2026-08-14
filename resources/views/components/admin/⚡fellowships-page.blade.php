<?php

use App\Models\Fellowship;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('برامج الزمالة | لوحة التحكم')]
class extends Component
{
    public ?string $savedMessage = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->canAdmin('applications.view'), 403);
    }

    #[Computed]
    public function fellowships()
    {
        return Fellowship::query()->orderBy('sort_order')->get();
    }

    public function toggleApplications(int $id): void
    {
        abort_unless(auth()->user()?->canAdmin('applications.review'), 403);

        $fellowship = Fellowship::query()->findOrFail($id);
        $fellowship->update(['application_open' => ! $fellowship->application_open]);
        $this->savedMessage = 'تم تحديث حالة التقديم.';
        unset($this->fellowships);
    }

    public function toggleStatus(int $id): void
    {
        abort_unless(auth()->user()?->canAdmin('applications.review'), 403);

        $fellowship = Fellowship::query()->findOrFail($id);
        $fellowship->update(['status' => $fellowship->status === 'open' ? 'closed' : 'open']);
        $this->savedMessage = 'تم تحديث حالة البرنامج.';
        unset($this->fellowships);
    }
};
?>

@include('partials.admin.shell-start', [
    'shellSidebarActive' => route('admin.fellowships'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['label' => 'برامج الزمالة'],
    ],
])

@if ($savedMessage)
    <div class="admin-alert admin-alert--info is-visible">{{ $savedMessage }}</div>
@endif

<section class="admin-crud-card">
    <div class="admin-crud-card__head admin-crud-card__head--row">
        <div>
            <h2>برامج الزمالة المهنية</h2>
            <p class="admin-crud-card__meta">تحكم في فتح/إغلاق التقديم — الطلبات في <a href="{{ route('admin.applications.fellowship') }}">طلبات الزمالة</a></p>
        </div>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>البرنامج</th>
                    <th>الحالة</th>
                    <th>التقديم</th>
                    <th>رابط التقديم</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($this->fellowships as $fellowship)
                    <tr>
                        <td>
                            <strong>{{ $fellowship->title_ar }}</strong>
                            @if ($fellowship->title_en)
                                <div class="admin-crud-card__meta" dir="ltr">{{ $fellowship->title_en }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="admin-badge admin-badge--{{ $fellowship->status === 'open' ? 'success' : 'muted' }}">
                                {{ $fellowship->status === 'open' ? 'مفتوح' : 'مغلق' }}
                            </span>
                        </td>
                        <td>
                            <span class="admin-badge admin-badge--{{ $fellowship->application_open ? 'success' : 'warn' }}">
                                {{ $fellowship->application_open ? 'يقبل طلبات' : 'متوقف' }}
                            </span>
                        </td>
                        <td dir="ltr">
                            <code>/ar/request/{{ $fellowship->slug }}</code>
                        </td>
                        <td>
                            <div class="admin-row-actions">
                                <a href="{{ route('admin.fellowships.form-fields', $fellowship) }}" class="admin-btn-primary admin-btn-primary--sm">حقول النموذج</a>
                                <a href="{{ route('admin.fellowships.edit', $fellowship) }}" class="admin-btn-secondary admin-btn-secondary--sm">تعديل</a>
                                <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="toggleApplications({{ $fellowship->id }})">
                                    {{ $fellowship->application_open ? 'إيقاف التقديم' : 'فتح التقديم' }}
                                </button>
                                <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="toggleStatus({{ $fellowship->id }})">
                                    {{ $fellowship->status === 'open' ? 'إغلاق البرنامج' : 'فتح البرنامج' }}
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>

@push('styles')
<style>.admin-row-actions{display:flex;flex-wrap:wrap;gap:.35rem;}</style>
@endpush

@include('partials.admin.shell-end')
