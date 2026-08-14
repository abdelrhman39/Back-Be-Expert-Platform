<?php

use App\Models\Statement;
use App\Services\StatementService;
use App\Support\StatementOptions;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin', [
    'adminPageTitle' => 'طلبات الإفادات',
    'adminPageDesc' => 'مراجعة وإصدار إفادات الطلاب',
    'adminLayout' => 'app',
])]
#[Title('الإفادات | لوحة التحكم')]
class extends Component
{
    use WithPagination;

    #[Url]
    public string $status = 'pending';

    #[Url(as: 'q')]
    public string $search = '';

    public string $rejectReason = '';

    public ?int $rejectId = null;

    public ?string $savedMessage = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->canAdmin('statements.manage'), 403);
    }

    public function issue(int $statementId): void
    {
        abort_unless(auth()->user()?->canAdmin('statements.manage'), 403);
        $statement = Statement::query()->findOrFail($statementId);
        app(StatementService::class)->issue($statement, auth()->user());
        $this->savedMessage = 'تم إصدار الإفادة '.$statement->reference_no;
    }

    public function startReject(int $statementId): void
    {
        abort_unless(auth()->user()?->canAdmin('statements.manage'), 403);
        $this->rejectId = $statementId;
        $this->rejectReason = '';
    }

    public function confirmReject(): void
    {
        abort_unless(auth()->user()?->canAdmin('statements.manage'), 403);
        $this->validate(['rejectReason' => ['required', 'string', 'min:5', 'max:500']]);

        $statement = Statement::query()->findOrFail($this->rejectId);
        app(StatementService::class)->reject($statement, auth()->user(), $this->rejectReason);

        $this->rejectId = null;
        $this->rejectReason = '';
        $this->savedMessage = 'تم رفض الطلب.';
    }

    public function getListProperty()
    {
        return app(StatementService::class)->adminList($this->status, $this->search);
    }
};
?>

@include('partials.admin.shell-start', [
    'shellLayout' => 'app',
    'shellSidebarActive' => route('admin.statements'),
])

@if ($savedMessage)
    <div class="admin-alert admin-alert--info is-visible">{{ $savedMessage }}</div>
@endif

<section class="admin-crud-card">
    <div class="admin-crud-card__head">
        <h2>طلبات الإفادات</h2>
    </div>

    <div class="admin-filter-grid" style="margin-bottom: 1rem; grid-template-columns: 1fr 1fr;">
        <div class="admin-field">
            <select class="admin-control" wire:model.live="status">
                <option value="all">كل الحالات</option>
                @foreach (StatementOptions::statuses() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="admin-field">
            <input type="search" class="admin-control" wire:model.live.debounce.300ms="search" placeholder="بحث...">
        </div>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>المرجع</th>
                    <th>الطالب</th>
                    <th>النوع</th>
                    <th>التاريخ</th>
                    <th>الحالة</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($this->list as $statement)
                    <tr wire:key="st-{{ $statement->id }}">
                        <td dir="ltr">{{ $statement->reference_no }}</td>
                        <td>{{ $statement->user?->displayName() ?? '—' }}</td>
                        <td>{{ StatementOptions::typeLabel($statement->type) }}</td>
                        <td>{{ $statement->requested_at?->format('Y-m-d') }}</td>
                        <td>{{ StatementOptions::statusLabel($statement->status) }}</td>
                        <td>
                            @if ($statement->isPending())
                                <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="issue({{ $statement->id }})">إصدار</button>
                                <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="startReject({{ $statement->id }})">رفض</button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $this->list->links() }}
</section>

@if ($rejectId)
    <section class="admin-crud-card">
        <div class="admin-crud-card__head"><h2>سبب الرفض</h2></div>
        <textarea class="admin-control" rows="3" wire:model="rejectReason"></textarea>
        @error('rejectReason')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
        <div class="admin-filter-actions" style="margin-top: 0.75rem;">
            <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="confirmReject">تأكيد الرفض</button>
            <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="$set('rejectId', null)">إلغاء</button>
        </div>
    </section>
@endif

@include('partials.admin.shell-end')
