@php
    $isoModel = $isoModel ?? 'phoneDialIso';
    $selectedIso = $selectedIso ?? ($this->{$isoModel} ?? \App\Support\CountryDialCodes::DEFAULT_ISO);
    $countries = \App\Support\CountryDialCodes::all();
    $selected = \App\Support\CountryDialCodes::find($selectedIso)
        ?? \App\Support\CountryDialCodes::find(\App\Support\CountryDialCodes::DEFAULT_ISO);
    $isEn = app()->getLocale() === 'en';
@endphp
<div
    class="country-dial"
    x-data="{
        open: false,
        query: '',
        iso: @js($selected['iso']),
        countries: @js($countries),
        get selected() {
            return this.countries.find((country) => country.iso === this.iso) || this.countries[0];
        },
        get filtered() {
            const q = this.query.trim().toLowerCase().replace(/^\+/, '');
            if (! q) return this.countries;
            return this.countries.filter((country) =>
                country.iso.toLowerCase().includes(q) ||
                country.dial.includes(q) ||
                country.name_ar.includes(this.query.trim()) ||
                country.name_en.toLowerCase().includes(q)
            );
        },
        pick(code) {
            this.iso = code;
            this.open = false;
            this.query = '';
            $wire.set(@js($isoModel), code);
        }
    }"
    @keydown.escape.window="open = false"
    @click.outside="open = false"
>
    <input type="hidden" wire:model="{{ $isoModel }}">
    <button
        type="button"
        class="country-dial__btn"
        dir="ltr"
        @click="open = !open; if (open) $nextTick(() => $refs.search?.focus())"
        :aria-expanded="open.toString()"
        aria-haspopup="listbox"
        aria-label="{{ $isEn ? 'Country calling code' : 'مفتاح الدولة' }}"
    >
        <span class="country-dial__flag" x-text="selected.flag">{{ $selected['flag'] }}</span>
        <span class="country-dial__code" x-text="'+' + selected.dial">+{{ $selected['dial'] }}</span>
        <i class="fa-solid fa-chevron-down country-dial__caret" aria-hidden="true"></i>
    </button>
    <div class="country-dial__panel" x-show="open" x-cloak x-transition.opacity.duration.120ms role="listbox">
        <div class="country-dial__search">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <input
                type="search"
                x-model="query"
                x-ref="search"
                @keydown.enter.prevent="if (filtered[0]) pick(filtered[0].iso)"
                :placeholder="{{ json_encode($isEn ? 'Search country or code' : 'ابحث باسم الدولة أو المفتاح') }}"
                autocomplete="off"
            >
        </div>
        <ul class="country-dial__list">
            <template x-for="country in filtered" :key="country.iso">
                <li>
                    <button
                        type="button"
                        class="country-dial__option"
                        :class="{ 'is-active': country.iso === iso }"
                        @click="pick(country.iso)"
                    >
                        <span class="country-dial__flag" x-text="country.flag"></span>
                        <span class="country-dial__name" x-text="{{ $isEn ? 'country.name_en' : 'country.name_ar' }}"></span>
                        <span class="country-dial__code" dir="ltr" x-text="'+' + country.dial"></span>
                    </button>
                </li>
            </template>
        </ul>
        <p class="country-dial__empty" x-show="filtered.length === 0" x-cloak>
            {{ $isEn ? 'No matching country' : 'لا توجد دولة مطابقة' }}
        </p>
    </div>
</div>
