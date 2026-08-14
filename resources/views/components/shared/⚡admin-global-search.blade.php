<?php

use App\Services\AdminGlobalSearchService;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public string $query = '';

    public bool $open = false;

    public function updatedQuery(): void
    {
        if (! $this->open) {
            $this->open = true;
        }
    }

    public function openSearch(): void
    {
        $this->open = true;
    }

    public function closeSearch(): void
    {
        $this->open = false;
    }

    public function clear(): void
    {
        $this->query = '';
        $this->open = true;
    }

    #[Computed]
    public function groups(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        return app(AdminGlobalSearchService::class)->search($user, $this->query);
    }
};
?>

@php
    $groups = $this->groups;
    $flatCount = collect($groups)->sum(fn ($g) => count($g['items'] ?? []));
@endphp

<div
    class="admin-gs"
    x-data="{
        open: @entangle('open').live,
        active: -1,
        openPanel() {
            this.open = true;
            this.active = -1;
            this.$nextTick(() => this.$refs.input && this.$refs.input.focus());
        },
        closePanel() {
            this.open = false;
            this.active = -1;
        },
        items() {
            return Array.from(this.$root.querySelectorAll('a.admin-gs__item'));
        },
        move(delta) {
            const list = this.items();
            if (!list.length) return;
            this.active = (this.active + delta + list.length) % list.length;
            list.forEach((el, i) => el.classList.toggle('is-active', i === this.active));
            list[this.active] && list[this.active].scrollIntoView({ block: 'nearest' });
        },
        activate() {
            const list = this.items();
            const target = list[this.active] || list[0];
            if (target && target.href) window.location.href = target.href;
        },
        onHotkey(e) {
            if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) {
                e.preventDefault();
                this.openPanel();
            }
        }
    }"
    x-on:keydown.window="onHotkey($event)"
>
    <button type="button" class="admin-gs__trigger" @click="openPanel()" aria-label="فتح البحث الشامل في المنصة">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3-3"/></svg>
        <span class="admin-gs__trigger-text">بحث في المنصة...</span>
        <kbd class="admin-gs__kbd">Ctrl&nbsp;K</kbd>
    </button>

    <div
        class="admin-gs__overlay"
        x-show="open"
        x-cloak
        x-transition.opacity.duration.120ms
        @keydown.escape.window="closePanel()"
    >
        <div class="admin-gs__backdrop" @click="closePanel()"></div>

        <div class="admin-gs__dialog" role="dialog" aria-modal="true" aria-label="البحث الشامل">
            <div class="admin-gs__bar">
                <svg class="admin-gs__bar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3-3"/></svg>
                <input
                    type="search"
                    class="admin-gs__input"
                    placeholder="ابحث عن طالب، عميل، طلب، دورة، صفحة، شهادة..."
                    aria-label="البحث الشامل"
                    wire:model.live.debounce.220ms="query"
                    x-ref="input"
                    @keydown.arrow-down.prevent="move(1)"
                    @keydown.arrow-up.prevent="move(-1)"
                    @keydown.enter.prevent="activate()"
                    @keydown.escape.prevent="closePanel()"
                    autocomplete="off"
                    spellcheck="false"
                >
                <div class="admin-gs__bar-actions">
                    @if (filled($query))
                        <button type="button" class="admin-gs__clear" wire:click="clear">مسح</button>
                    @endif
                    <button type="button" class="admin-gs__close" @click="closePanel()" aria-label="إغلاق">×</button>
                </div>
            </div>

            <div class="admin-gs__body">
                <div class="admin-gs__loading" wire:loading.flex wire:target="query">جاري البحث...</div>

                <div wire:loading.remove wire:target="query">
                    @foreach ($groups as $group)
                        <section class="admin-gs__group">
                            <header class="admin-gs__group-label">{{ $group['label'] }}</header>
                            @if (($group['items'] ?? []) === [])
                                <p class="admin-gs__empty-line">ابدأ بالكتابة لعرض الاقتراحات الذكية...</p>
                            @else
                                <ul class="admin-gs__list">
                                    @foreach ($group['items'] as $item)
                                        @if (($item['type'] ?? '') === 'empty')
                                            <li>
                                                <div class="admin-gs__item admin-gs__item--empty">
                                                    <span class="admin-gs__icon">@include('partials.admin.global-search-icon', ['icon' => $item['icon']])</span>
                                                    <span class="admin-gs__meta">
                                                        <strong>{{ $item['title'] }}</strong>
                                                        <small>{{ $item['subtitle'] }}</small>
                                                    </span>
                                                </div>
                                            </li>
                                        @else
                                            <li>
                                                <a href="{{ $item['url'] }}" class="admin-gs__item">
                                                    <span class="admin-gs__icon admin-gs__icon--{{ $item['icon'] }}">@include('partials.admin.global-search-icon', ['icon' => $item['icon']])</span>
                                                    <span class="admin-gs__meta">
                                                        <strong>{{ $item['title'] }}</strong>
                                                        @if (filled($item['subtitle']))
                                                            <small>{{ $item['subtitle'] }}</small>
                                                        @endif
                                                    </span>
                                                    @if (filled($item['badge'] ?? null))
                                                        <span class="admin-gs__badge">{{ $item['badge'] }}</span>
                                                    @endif
                                                    <span class="admin-gs__go" aria-hidden="true">↵</span>
                                                </a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            @endif
                        </section>
                    @endforeach
                </div>
            </div>

            <footer class="admin-gs__foot">
                <span><kbd>↑</kbd> <kbd>↓</kbd> تنقل</span>
                <span><kbd>Enter</kbd> فتح</span>
                <span><kbd>Esc</kbd> إغلاق</span>
                <span class="admin-gs__count">{{ $flatCount }} نتيجة</span>
            </footer>
        </div>
    </div>
</div>
