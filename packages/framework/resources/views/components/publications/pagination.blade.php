@php/** @var \Hyde\Framework\Features\Publications\Paginator $paginator */@endphp

<nav class="flex justify-between">
    @if($paginator->previous())
        <x-link :href="$paginator->previous()">Prev</x-link>
    @else
        <span class="opacity-75">Prev</span>
    @endif

    <div>
        @foreach($paginator->getNumbersArray() as $pageNumber => $destination)
            @if($paginator->currentPage() === $pageNumber)
                <span class="mx-1"><strong>{{ $pageNumber }}</strong></span>
            @else
                <span class="mx-1"><x-link :href="$destination">{{ $pageNumber }}</x-link>
            @endif
        @endforeach
    </div>

    @if($paginator->next())
        <x-link :href="$paginator->next()">Next</x-link>
    @else
        <span class="opacity-75">Next</span>
    @endif
</nav>
