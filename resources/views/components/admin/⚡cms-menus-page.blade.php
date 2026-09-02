<?php

use App\Models\CmsMenu;
use App\Models\CmsMenuItem;
use App\Models\CmsPage;
use App\Services\CmsMenuService;
use App\Support\CmsOptions;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Layout('layouts.admin', ['adminLayout' => 'app'])]
#[Title('قوائم التنقل | لوحة التحكم')]
class extends Component
{
    #[Url]
    public string $menuKey = 'header_main';

    public bool $showForm = false;

    public ?int $editingItemId = null;

    public ?int $parentId = null;

    public string $labelAr = '';

    public string $labelEn = '';

    public string $linkType = 'route';

    public string $routeName = '';

    public ?int $pageId = null;

    public string $url = '';

    public bool $openInNewTab = false;

    public bool $isActive = true;

    public string $permission = '';

    public int $sortOrder = 0;

    public ?string $savedMessage = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->canAdmin('menus.view'), 403);
    }

    public function updatedMenuKey(): void
    {
        $this->resetForm();
    }

    public function openCreate(?int $parentId = null): void
    {
        abort_unless(auth()->user()?->canAdmin('menus.manage'), 403);
        $this->resetForm();
        $this->parentId = $parentId;
        $this->showForm = true;
    }

    public function openEdit(int $itemId): void
    {
        abort_unless(auth()->user()?->canAdmin('menus.manage'), 403);

        $item = CmsMenuItem::query()->findOrFail($itemId);
        $this->editingItemId = $item->id;
        $this->parentId = $item->parent_id;
        $this->labelAr = $item->label_ar;
        $this->labelEn = $item->label_en ?? '';
        $this->linkType = $item->link_type;
        $this->routeName = $item->route_name ?? '';
        $this->pageId = $item->page_id;
        $this->url = $item->url ?? '';
        $this->openInNewTab = $item->open_in_new_tab;
        $this->isActive = $item->is_active;
        $this->permission = $item->permission ?? '';
        $this->sortOrder = $item->sort_order;
        $this->showForm = true;
    }

    public function saveItem(CmsMenuService $menus): void
    {
        abort_unless(auth()->user()?->canAdmin('menus.manage'), 403);

        $menu = CmsMenu::query()->where('key', $this->menuKey)->firstOrFail();

        $this->validate([
            'labelAr' => ['required', 'string', 'max:255'],
            'linkType' => ['required', Rule::in(array_keys(CmsOptions::linkTypes()))],
            'routeName' => ['required_if:linkType,route', 'nullable', 'string', 'max:128'],
            'pageId' => ['required_if:linkType,page', 'nullable', 'exists:cms_pages,id'],
            'url' => ['required_if:linkType,url', 'nullable', 'string', 'max:512'],
            'sortOrder' => ['integer', 'min:0'],
        ], [], ['labelAr' => 'العنوان']);

        $item = $this->editingItemId ? CmsMenuItem::query()->find($this->editingItemId) : null;

        $menus->saveItem([
            'menu_id' => $menu->id,
            'parent_id' => $this->parentId,
            'sort_order' => $this->sortOrder,
            'label_ar' => $this->labelAr,
            'label_en' => $this->labelEn ?: null,
            'link_type' => $this->linkType,
            'route_name' => $this->linkType === 'route' ? $this->routeName : null,
            'page_id' => $this->linkType === 'page' ? $this->pageId : null,
            'url' => $this->linkType === 'url' ? $this->url : null,
            'open_in_new_tab' => $this->openInNewTab,
            'is_active' => $this->isActive,
            'permission' => $this->permission ?: null,
        ], $item);

        $this->savedMessage = 'تم حفظ عنصر القائمة.';
        $this->resetForm();
    }

    public function deleteItem(int $itemId, CmsMenuService $menus): void
    {
        abort_unless(auth()->user()?->canAdmin('menus.manage'), 403);
        $menus->deleteItem(CmsMenuItem::query()->findOrFail($itemId));
        $this->savedMessage = 'تم الحذف.';
    }

    public function moveItem(int $itemId, string $direction, CmsMenuService $menus): void
    {
        abort_unless(auth()->user()?->canAdmin('menus.manage'), 403);
        $menus->moveItem(CmsMenuItem::query()->findOrFail($itemId), $direction);
    }

    /** @param  array<int|string>  $orderedIds */
    public function reorderMenuItems(?int $parentId, array $orderedIds, CmsMenuService $menus): void
    {
        abort_unless(auth()->user()?->canAdmin('menus.manage'), 403);

        $menu = CmsMenu::query()->where('key', $this->menuKey)->firstOrFail();
        $menus->reorderItems($parentId, $orderedIds, $menu->id);
        unset($this->menu);
    }

    public function cancelForm(): void
    {
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->showForm = false;
        $this->editingItemId = null;
        $this->parentId = null;
        $this->reset([
            'labelAr', 'labelEn', 'routeName', 'url', 'permission',
        ]);
        $this->linkType = 'route';
        $this->openInNewTab = false;
        $this->isActive = true;
        $this->sortOrder = 0;
        $this->pageId = null;
    }

    #[Computed]
    public function menu()
    {
        return CmsMenu::query()->where('key', $this->menuKey)->with([
            'items' => fn ($q) => $q->with('children')->whereNull('parent_id')->orderBy('sort_order'),
        ])->first();
    }

    #[Computed]
    public function publishedPages()
    {
        return CmsPage::query()->with('translations')->where('status', 'published')->orderBy('sort_order')->get();
    }

    /** @return array<string, string> */
    public function routeOptions(): array
    {
        return [
            'home' => 'الرئيسية',
            'about' => 'عن المنصة',
            'contact' => 'تواصل معنا',
            'courses.index' => 'كل البرامج',
            'courses.certificates' => 'الشهادات الاحترافية',
            'courses.diplomas' => 'الدبلومات',
            'fellowships.index' => 'الزمالات المهنية',
            'register' => 'التسجيل',
            'login' => 'تسجيل الدخول',
            'cart' => 'السلة',
            'academic-registration' => 'التسجيل الأكاديمي',
        ];
    }
};
?>

@include('partials.admin.shell-start', [
    'shellSidebarActive' => route('admin.cms-menus'),
    'shellBreadcrumb' => [
        ['href' => route('admin.dashboard'), 'label' => 'الرئيسية'],
        ['label' => 'قوائم التنقل'],
    ],
])

@if ($savedMessage)
    <div class="admin-alert admin-alert--info is-visible">{{ $savedMessage }}</div>
@endif

<section class="admin-crud-card" style="margin-bottom:1rem;">
    <div class="admin-crud-card__head admin-crud-card__head--row">
        <div>
            <h2>قوائم التنقل</h2>
            <p class="admin-crud-card__meta">تحكم في هيدر الموقع وقوائم الفوتر — ترتيب، روابط، صلاحيات.</p>
        </div>
        @canAdmin('menus.manage')
            <button type="button" class="admin-btn-primary admin-btn-primary--sm" wire:click="openCreate">عنصر جديد</button>
        @endcanAdmin
    </div>

    <div class="admin-field" style="max-width:320px;">
        <label>اختر القائمة</label>
        <select class="admin-control" wire:model.live="menuKey">
            @foreach (CmsOptions::menuKeys() as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>
</section>

@if ($showForm)
    <section class="admin-crud-card" style="margin-bottom:1rem;">
        <div class="admin-crud-card__head"><h3>{{ $editingItemId ? 'تعديل عنصر' : 'عنصر جديد' }}</h3></div>
        <form wire:submit="saveItem">
            <div class="admin-filter-grid" style="grid-template-columns: repeat(2, 1fr);">
                <div class="admin-field">
                    <label>العنوان (عربي) *</label>
                    <input type="text" class="admin-control" wire:model="labelAr">
                    @error('labelAr')<div class="admin-field-hint is-visible">{{ $message }}</div>@enderror
                </div>
                <div class="admin-field">
                    <label>العنوان (إنجليزي)</label>
                    <input type="text" class="admin-control" wire:model="labelEn">
                </div>
                <div class="admin-field">
                    <label>نوع الرابط</label>
                    <select class="admin-control" wire:model.live="linkType">
                        @foreach (CmsOptions::linkTypes() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="admin-field">
                    <label>الترتيب</label>
                    <input type="number" min="0" class="admin-control" wire:model="sortOrder">
                </div>
                @if ($linkType === 'route')
                    <div class="admin-field">
                        <label>مسار Laravel</label>
                        <select class="admin-control" wire:model="routeName">
                            <option value="">—</option>
                            @foreach ($this->routeOptions() as $route => $label)
                                <option value="{{ $route }}">{{ $label }} ({{ $route }})</option>
                            @endforeach
                        </select>
                    </div>
                @elseif ($linkType === 'page')
                    <div class="admin-field">
                        <label>صفحة CMS</label>
                        <select class="admin-control" wire:model="pageId">
                            <option value="">—</option>
                            @foreach ($this->publishedPages as $p)
                                <option value="{{ $p->id }}">{{ $p->translate('ar')?->title }}</option>
                            @endforeach
                        </select>
                    </div>
                @elseif ($linkType === 'url')
                    <div class="admin-field admin-field--wide">
                        <label>رابط URL</label>
                        <input type="text" class="admin-control" wire:model="url" dir="ltr" placeholder="/{locale}/courses أو https://...">
                        <div class="admin-field-hint">للصفحات الداخلية استخدم نوع «مسار Laravel». يمكن كتابة <code dir="ltr">/{locale}/courses</code> ليتبدل حسب لغة الموقع.</div>
                    </div>
                @endif
                <div class="admin-field">
                    <label>صلاحية admin (اختياري)</label>
                    <input type="text" class="admin-control" wire:model="permission" dir="ltr" placeholder="pages.view">
                </div>
                <div class="admin-field" style="display:flex;gap:1rem;align-items:center;">
                    <label class="admin-checkbox"><input type="checkbox" wire:model="isActive"><span>نشط</span></label>
                    <label class="admin-checkbox"><input type="checkbox" wire:model="openInNewTab"><span>نافذة جديدة</span></label>
                </div>
            </div>
            <div class="admin-filter-actions">
                <button type="submit" class="admin-btn-primary admin-btn-primary--sm">حفظ</button>
                <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="cancelForm">إلغاء</button>
            </div>
        </form>
    </section>
@endif

<section class="admin-crud-card">
    <div class="admin-crud-card__head"><h3>عناصر القائمة</h3></div>
    @if ($this->menu)
        @include('partials.admin.cms-menu-tree', ['items' => $this->menu->items, 'depth' => 0, 'parentId' => null])
    @else
        <p class="admin-crud-card__meta">القائمة غير موجودة — شغّل CmsSeeder.</p>
    @endif
</section>

@script
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
    function initMenuSortables() {
        document.querySelectorAll('[data-menu-sortable]').forEach(list => {
            if (list.dataset.sortableInit) return;
            list.dataset.sortableInit = '1';
            const parentId = list.dataset.parentId ? parseInt(list.dataset.parentId, 10) : null;
            Sortable.create(list, {
                handle: '.admin-cms-drag-handle',
                animation: 150,
                onEnd() {
                    const ids = [...list.querySelectorAll('.admin-cms-menu-tree__item')].map(el => el.dataset.id);
                    $wire.reorderMenuItems(parentId, ids);
                },
            });
        });
    }
    initMenuSortables();
    Livewire.hook('morph.updated', () => initMenuSortables());
</script>
@endscript

@include('partials.admin.shell-end')
