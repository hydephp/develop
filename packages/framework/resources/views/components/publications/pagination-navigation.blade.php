@php/** @var \Hyde\Framework\Features\Paginator $paginator */@endphp
<nav class="flex justify-between mt-4">
    @if($paginator->previous())
        <x-link :href="$paginator->previous()">Prev</x-link>
    @else
        <span class="opacity-75">Prev</span>
    @endif

    <div>
        @foreach($paginator->getPageLinks() as $pageNumber => $destination)
            @if($paginator->currentPage() === $pageNumber)
                <strong>{{ $pageNumber }}</strong>
            @else
                <x-link :href="$destination">{{ $pageNumber }}</x-link>
            @endif
        @endforeach
    </div>

    @if($paginator->next())
        <x-link :href="$paginator->next()">Next</x-link>
    @else
        <span class="opacity-75">Next</span>
    @endif
</nav>