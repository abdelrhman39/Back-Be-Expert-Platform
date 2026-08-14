@use('App\Support\CmsOptions')

<ul
    class="admin-cms-menu-tree"
    @if ($depth > 0) style="margin-right:1.25rem;border-right:2px solid #e2e8f0;padding-right:0.75rem;" @endif
    data-menu-sortable
    data-parent-id="{{ $parentId ?? '' }}"
>
    @forelse ($items as $item)
        <li class="admin-cms-menu-tree__item" wire:key="mi-{{ $item->id }}" data-id="{{ $item->id }}">
            <div class="admin-cms-menu-tree__row">
                @canAdmin('menus.manage')
                    <button type="button" class="admin-cms-drag-handle" title="اسحب لإعادة الترتيب">⠿</button>
                @endcanAdmin
                <span>
                    <strong>{{ $item->label_ar }}</strong>
                    <span class="admin-crud-card__meta"> — {{ CmsOptions::linkTypes()[$item->link_type] ?? $item->link_type }}
                        @if ($item->link_type === 'route') · {{ $item->route_name }} @endif
                        @if (! $item->is_active) · <em>معطّل</em> @endif
                    </span>
                </span>
                @canAdmin('menus.manage')
                    <span class="admin-cms-menu-tree__actions">
                        <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="moveItem({{ $item->id }}, 'up')">↑</button>
                        <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="moveItem({{ $item->id }}, 'down')">↓</button>
                        <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="openCreate({{ $item->id }})">فرعي +</button>
                        <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="openEdit({{ $item->id }})">تعديل</button>
                        <button type="button" class="admin-btn-secondary admin-btn-secondary--sm" wire:click="deleteItem({{ $item->id }})" wire:confirm="حذف العنصر؟">حذف</button>
                    </span>
                @endcanAdmin
            </div>
            @if ($item->children->isNotEmpty())
                @include('partials.admin.cms-menu-tree', ['items' => $item->children, 'depth' => $depth + 1, 'parentId' => $item->id])
            @endif
        </li>
    @empty
        <li class="admin-crud-card__meta">لا عناصر في هذه القائمة.</li>
    @endforelse
</ul>

<style>
.admin-cms-menu-tree { list-style: none; padding: 0; margin: 0; }
.admin-cms-menu-tree__item { margin-bottom: 0.5rem; }
.admin-cms-menu-tree__row { display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9; flex-wrap: wrap; }
.admin-cms-menu-tree__actions { display: flex; gap: 0.25rem; flex-wrap: wrap; }
.admin-cms-drag-handle { border: none; background: transparent; cursor: grab; color: #64748b; font-size: 1rem; padding: 0 0.25rem; }
.admin-cms-drag-handle:active { cursor: grabbing; }
</style>
