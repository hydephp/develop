@extends('hyde::layouts.app')
@section('content')
    <main id="content" class="mx-auto max-w-7xl py-16 px-8">
        <div class="prose dark:prose-invert">
            @php/** @var \Hyde\Framework\Features\Publications\Models\PublicationType $publicationType*/@endphp
            <h1>Publications for type {{ $publicationType->name }}</h1>
            <ol>
                @foreach($publicationType->getPublications()->take($publicationType->pageSize()) as $publication)
                    <li>
                        <x-link :href="$publication->getRoute()">{{ $publication->title }}</x-link>
                    </li>
                @endforeach
            </ol>
            @if($publicationType->usesPagination())
                @include('hyde::components.publications.pagination', ['paginator' => $publicationType->getPaginator(currentPageNumber: $page->matter('paginatorPage'))])
            @endif
        </div>
    </main>
@endsection
