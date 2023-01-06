@extends('hyde::layouts.app')
@section('content')
    <main id="content" class="mx-auto max-w-7xl py-16 px-8">
        <div class="prose dark:prose-invert">
            <h1>Publications for type {{ $page->type->name }}</h1>
            <ol>
                @php/** @var \Hyde\Pages\PublicationPage $publication*/@endphp
                @foreach($publications as $publication)
                    <li>
                        <x-link :href="$publication->getRoute()">{{ $publication->title }}</x-link>
                    </li>
                @endforeach
            </ol>
        </div>

        // todo if paginated add pagination navigation here too for the index page (and extract component)
    </main>
@endsection
