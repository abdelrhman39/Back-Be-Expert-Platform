@php
if (! isset($scrollTo)) {
    $scrollTo = 'body';
}

$scrollIntoViewJsSnippet = ($scrollTo !== false)
    ? <<<JS
       (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
    JS
    : '';
@endphp

@if ($paginator->hasPages())
    <nav class="admin-pagination" role="navigation" aria-label="ترقيم الصفحات">
        <span class="admin-pagination__info">
            @if ($paginator->total() > 0)
                عرض {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} من {{ $paginator->total() }} نتيجة
                · صفحة {{ $paginator->currentPage() }} من {{ $paginator->lastPage() }}
            @endif
        </span>

        <div class="admin-pagination__btns">
            @if ($paginator->onFirstPage())
                <button type="button" class="admin-pagination__btn" disabled aria-disabled="true">السابق</button>
            @else
                <button
                    type="button"
                    class="admin-pagination__btn"
                    wire:click="previousPage('{{ $paginator->getPageName() }}')"
                    x-on:click="{{ $scrollIntoViewJsSnippet }}"
                    wire:loading.attr="disabled"
                    dusk="previousPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}"
                >السابق</button>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="admin-pagination__ellipsis" aria-hidden="true">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <button type="button" class="admin-pagination__btn is-active" aria-current="page" wire:key="paginator-{{ $paginator->getPageName() }}-page-{{ $page }}">{{ $page }}</button>
                        @else
                            <button
                                type="button"
                                class="admin-pagination__btn"
                                wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                x-on:click="{{ $scrollIntoViewJsSnippet }}"
                                wire:key="paginator-{{ $paginator->getPageName() }}-page-{{ $page }}"
                            >{{ $page }}</button>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <button
                    type="button"
                    class="admin-pagination__btn"
                    wire:click="nextPage('{{ $paginator->getPageName() }}')"
                    x-on:click="{{ $scrollIntoViewJsSnippet }}"
                    wire:loading.attr="disabled"
                    dusk="nextPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}"
                >التالي</button>
            @else
                <button type="button" class="admin-pagination__btn" disabled aria-disabled="true">التالي</button>
            @endif
        </div>
    </nav>
@endif
