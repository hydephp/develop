@php/** @var \Hyde\Framework\Features\Publications\PaginationService $paginator */@endphp

<nav class="flex justify-between">
    @if($paginator->previous())
        <x-link :href="$paginator->previous()">Prev</x-link>
    @else
        <span class="opacity-75">Prev</span>
    @endif

    <div>
        @foreach(range(1, $paginator->totalPages()) as $pageNumber)
            @if($paginator->currentPage() === $pageNumber)
                <span class="mx-1"><strong>{{ $pageNumber }}</strong></span>
            @else
                // TODO
            @endif
        @endforeach
    </div>

    @if($paginator->next())
        <x-link :href="$paginator->next()">Next</x-link>
    @else
        <span class="opacity-75">Next</span>
    @endif
</nav>
