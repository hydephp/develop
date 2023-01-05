@extends('hyde::layouts.app')
@section('content')
    <main id="content" class="mx-auto max-w-7xl py-16 px-8">
        <div class="prose dark:prose-invert">
            <h1>Publications for type {{ $type->name }} (Page - {{ $paginator->current }})</h1>
            <ol start="{{ $paginator->offset }}">
                @php/** @var \Hyde\Pages\PublicationPage $publication*/@endphp
                @foreach($publications as $publication)
                    <li>
                        <x-link :href="$publication->getRoute()">{{ $publication->title }}</x-link>
                    </li>
                @endforeach
            </ol>

            //  next prev links + page numbers
        </div>
    </main>
@endsection
