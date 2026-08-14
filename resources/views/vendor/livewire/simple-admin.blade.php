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
