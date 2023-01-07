@php/** @var \Hyde\Framework\Features\Publications\Models\PublicationType $publicationType*/@endphp
@extends('hyde::layouts.app')
@section('content')
    <main id="content" class="mx-auto max-w-7xl py-16 px-8">
        <div class="prose dark:prose-invert">
            <h1>Publications for type {{ $publicationType->name }}</h1>
            @php
                $paginator = $publicationType->getPaginator($page->matter('paginatorPage'));
            @endphp
            <ol start="{{ $paginator->itemsStartNumber() }}">
                @foreach($paginator->getItemsForPage() as $publication)
                    <li>
                        <x-link :href="$publication->getRoute()">{{ $publication->title }}</x-link>
                    </li>
                @endforeach
            </ol>

            @include('hyde::components.publications.pagination')
        </div>
    </main>
@endsection
