@use('App\Support\CmsOptions')
@use('Illuminate\Support\Str')

<div class="cms-hub-table-wrap">
    <table class="cms-hub-table">
        <thead>
            <tr>
                <th class="cms-hub-table__col-id">#</th>
                <th class="cms-hub-table__col-page">الصفحة</th>
                <th class="cms-hub-table__col-meta">التصنيف</th>
                <th class="cms-hub-table__col-status">الحالة</th>
                <th class="cms-hub-table__col-date">آخر تحديث</th>
                <th class="cms-hub-table__col-actions">إجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pages as $page)
                @php
                    $tAr = $page->translate('ar');
                    $tEn = $page->translate('en');
                    $publicUrl = $cms->publicUrl($page, 'ar');
                    $title = $tAr?->title ?? $tEn?->title ?? '—';
                    $typeIcon = match ($page->type) {
                        'policy' => '§',
                        'home' => '⌂',
                        'about' => 'ℹ',
                        'contact' => '✉',
                        'landing' => '⚡',
                        default => '📄',
                    };
                @endphp
                <tr wire:key="cms-row-{{ $page->id }}" @class(['cms-hub-table__row', 'cms-hub-table__row--'.$page->status])>
                    <td class="cms-hub-table__id">{{ $page->id }}</td>

                    <td class="cms-hub-table__page">
                        <div class="cms-hub-table__page-cell">
                            <span class="cms-hub-table__type-icon" title="{{ CmsOptions::pageTypes()[$page->type] ?? $page->type }}">{{ $typeIcon }}</span>
                            <div class="cms-hub-table__page-body">
                                <div class="cms-hub-table__title-row">
                                    <a href="{{ route('admin.cms-pages.edit', $page) }}" class="cms-hub-table__title">{{ $title }}</a>
                                    <span class="cms-hub-table__langs">
                                        @if ($tAr)<span class="cms-hub-table__lang cms-hub-table__lang--ar">AR</span>@endif
                                        @if ($tEn)<span class="cms-hub-table__lang cms-hub-table__lang--en">EN</span>@endif
                                    </span>
                                </div>
                                @if ($tAr?->slug)
                                    <code class="cms-hub-table__slug" dir="ltr">/ar/page/{{ $tAr->slug }}</code>
                                @endif
                            </div>
                        </div>
                    </td>

                    <td class="cms-hub-table__meta">
                        <span class="cms-hub-table__pill cms-hub-table__pill--type">{{ CmsOptions::pageTypes()[$page->type] ?? $page->type }}</span>
                        <span class="cms-hub-table__pill cms-hub-table__pill--layout">{{ Str::limit(CmsOptions::pageLayouts()[$page->layout ?? 'default'] ?? 'default', 14) }}</span>
                        <span class="cms-hub-table__pill cms-hub-table__pill--mode">
                            {{ ($page->content_mode ?? 'html') === 'blocks' ? 'بلوكات' : 'HTML' }}
                        </span>
                    </td>

                    <td class="cms-hub-table__status">
                        <span @class(['cms-hub-table__status-badge', 'is-'.$page->status])>
                            {{ CmsOptions::pageStatuses()[$page->status] ?? $page->status }}
                        </span>
                        @if ($page->show_in_footer || ($page->noindex ?? false) || ! ($page->show_title ?? true))
                            <div class="cms-hub-table__mini-flags">
                                @if ($page->show_in_footer)<span class="cms-hub-table__mini-flag">فوتر</span>@endif
                                @if ($page->noindex ?? false)<span class="cms-hub-table__mini-flag cms-hub-table__mini-flag--warn">NOINDEX</span>@endif
                                @if (! ($page->show_title ?? true))<span class="cms-hub-table__mini-flag">بدون عنوان</span>@endif
                            </div>
                        @endif
                    </td>

                    <td class="cms-hub-table__date">
                        <span class="cms-hub-table__date-line">{{ $page->updated_at?->format('Y-m-d') }} · {{ $page->updated_at?->format('H:i') }}</span>
                    </td>

                    <td class="cms-hub-table__actions">
                        @canAdmin('pages.manage')
                            <div class="cms-hub-table__toolbar">
                                <a href="{{ route('admin.cms-pages.edit', $page) }}" class="cms-hub-table__icon-btn cms-hub-table__icon-btn--primary" title="تعديل">✎</a>
                                <a href="{{ route('admin.cms-pages.preview', ['page' => $page->id, 'locale' => 'ar']) }}" class="cms-hub-table__icon-btn" target="_blank" rel="noopener" title="معاينة">👁</a>
                                @if ($publicUrl)
                                    <a href="{{ $publicUrl }}" class="cms-hub-table__icon-btn" target="_blank" rel="noopener" title="عرض عام">↗</a>
                                @endif
                                @if ($page->type !== 'home')
                                    <button
                                        type="button"
                                        class="cms-hub-table__text-btn cms-hub-table__text-btn--danger"
                                        wire:click="deletePage({{ $page->id }})"
                                        wire:confirm="حذف هذه الصفحة نهائياً؟"
                                        title="حذف"
                                    >حذف</button>
                                @endif

                                <details class="cms-hub-table__more">
                                    <summary class="cms-hub-table__icon-btn cms-hub-table__icon-btn--more" title="المزيد">⋮</summary>
                                    <div class="cms-hub-table__menu" role="menu">
                                        <button type="button" class="cms-hub-table__menu-item" wire:click="toggleStatus({{ $page->id }})" role="menuitem">
                                            {{ $page->status === 'published' ? '↩ إرجاع لمسودة' : '✓ نشر الصفحة' }}
                                        </button>
                                        <button type="button" class="cms-hub-table__menu-item" wire:click="duplicatePage({{ $page->id }})" role="menuitem">⧉ نسخ الصفحة</button>
                                    </div>
                                </details>
                            </div>
                        @endcanAdmin
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        <div class="cms-hub-table__empty">
                            <span class="cms-hub-table__empty-icon">📭</span>
                            <p>لا توجد صفحات مطابقة.</p>
                            @canAdmin('pages.manage')
                                <a href="{{ route('admin.cms-pages.create') }}" class="admin-btn-primary admin-btn-primary--sm">+ صفحة جديدة</a>
                            @endcanAdmin
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('click', (e) => {
                if (! e.target.closest('.cms-hub-table__more')) {
                    document.querySelectorAll('.cms-hub-table__more[open]').forEach((el) => el.removeAttribute('open'));
                }
            });
        </script>
    @endpush
@endonce
