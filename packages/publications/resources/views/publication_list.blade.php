@php /** @var \Hyde\Publications\Models\PublicationType $publicationType*/ @endphp
@extends('hyde::layouts.app')
@section('content')
    <main id="content" class="mx-auto max-w-7xl py-16 px-8">
        <div class="prose dark:prose-invert">
            <h1>Publications for type {{ $publicationType->name }}</h1>

            <ol>
                @foreach($publicationType->getPublications() as $publication)
                    <li>
                        <x-link :href="$publication->getRoute()">{{ $publication->title }}</x-link>
                    </li>
                @endforeach
            </ol>
        </div>
    </main>
@endsection
