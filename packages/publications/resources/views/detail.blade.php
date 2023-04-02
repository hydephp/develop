@php/** @var \Hyde\Publications\Models\PublicationPage $publication*/@endphp
@extends('hyde::layouts.app')
@section('content')
    <main id="content" class="mx-auto max-w-7xl py-16 px-8">
        <article class="prose dark:prose-invert">
            <h1>{{ $publication->title }}</h1>
            <p>
                {{ $publication->markdown }}
            </p>
        </article>

        <div class="prose dark:prose-invert my-8">
            <hr>
        </div>

        <article class="prose dark:prose-invert">
            <h3>Front Matter Data</h3>
            <div class="ml-4">
                @foreach($publication->matter->toArray() as $key => $value)
                    <dt class="font-bold">{{ $key }}</dt>
                    <dd class="ml-4">
                        {{ is_array($value) ? ('(array) '. implode(', ', $value)) : $value }}
                    </dd>
                @endforeach
            </div>
        </article>
    </main>
@endsection
