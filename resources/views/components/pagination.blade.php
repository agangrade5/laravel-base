@if($paginator->hasPages())
<div class="card-footer clearfix">
    {{-- Showing Records --}}
    <div class="float-start pt-1 fs-7 text-body-secondary">
        Showing
        {{ $paginator->firstItem() ?? 0 }}
        to
        {{ $paginator->lastItem() ?? 0 }}
        of
        {{ $paginator->total() }}
        {{ $label ?? 'records' }}
    </div>

    <ul class="pagination pagination-sm m-0 float-end">

        {{-- Previous --}}
        @if($paginator->onFirstPage())
            <li class="page-item disabled">
                <span class="page-link" aria-label="Previous">&laquo;</span>
            </li>
        @else
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->previousPageUrl() }}" aria-label="Previous">&laquo;</a>
            </li>
        @endif

        {{-- First set --}}
        @if(is_array($elements['first'] ?? null))
            @foreach($elements['first'] as $page => $url)
                <li class="page-item {{ $page == $paginator->currentPage() ? 'active' : '' }}">
                    @if($page == $paginator->currentPage())
                        <span class="page-link">{{ $page }}</span>
                    @else
                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                    @endif
                </li>
            @endforeach
        @endif

        {{-- Dots before slider --}}
        @if(is_array($elements['slider'] ?? null) && is_array($elements['first'] ?? null) && array_key_first($elements['slider']) > array_key_last($elements['first']) + 1)
            <li class="page-item disabled"><span class="page-link">...</span></li>
        @endif

        {{-- Slider --}}
        @if(is_array($elements['slider'] ?? null))
            @foreach($elements['slider'] as $page => $url)
                <li class="page-item {{ $page == $paginator->currentPage() ? 'active' : '' }}">
                    @if($page == $paginator->currentPage())
                        <span class="page-link">{{ $page }}</span>
                    @else
                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                    @endif
                </li>
            @endforeach
        @endif

        {{-- Dots before last --}}
        @if(is_array($elements['last'] ?? null))
            <li class="page-item disabled"><span class="page-link">...</span></li>
        @endif

        {{-- Last set --}}
        @if(is_array($elements['last'] ?? null))
            @foreach($elements['last'] as $page => $url)
                <li class="page-item {{ $page == $paginator->currentPage() ? 'active' : '' }}">
                    @if($page == $paginator->currentPage())
                        <span class="page-link">{{ $page }}</span>
                    @else
                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                    @endif
                </li>
            @endforeach
        @endif

        {{-- Next --}}
        @if($paginator->hasMorePages())
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->nextPageUrl() }}" aria-label="Next">&raquo;</a>
            </li>
        @else
            <li class="page-item disabled">
                <span class="page-link" aria-label="Next">&raquo;</span>
            </li>
        @endif
    </ul>
</div>
@endif
