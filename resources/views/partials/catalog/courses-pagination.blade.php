@if ($paginator->hasPages())
    <div class="col-md-12">
        <div class="pagination">
            <ul>
                <li>
                    @if ($paginator->onFirstPage())
                        <a href="javascript:void(0);" class="disabled"><i class="fa-solid fa-chevron-left"></i></a>
                    @else
                        <a href="javascript:void(0);" wire:click.prevent="previousPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" class="prev"><i class="fa-solid fa-chevron-left"></i></a>
                    @endif
                </li>

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <li><span>{{ $element }}</span></li>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            <li>
                                @if ($page == $paginator->currentPage())
                                    <a href="javascript:void(0);" class="active">{{ $page }}</a>
                                @else
                                    <a href="javascript:void(0);" wire:click.prevent="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')">{{ $page }}</a>
                                @endif
                            </li>
                        @endforeach
                    @endif
                @endforeach

                <li>
                    @if ($paginator->hasMorePages())
                        <a href="javascript:void(0);" wire:click.prevent="nextPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" class="next"><i class="fa-solid fa-chevron-right"></i></a>
                    @else
                        <a href="javascript:void(0);" class="disabled"><i class="fa-solid fa-chevron-right"></i></a>
                    @endif
                </li>
            </ul>
        </div>
    </div>
@endif
