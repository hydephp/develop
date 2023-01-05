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

            <nav>
                @if($paginator->previous)
{{--                    useful if using routes: <x-link :href="$paginator->previous">Prev</x-link>--}}
                    <a href="page-{{ $paginator->previous }}.html">Prev</a> <!-- fixme support pretty urls -->
                @endif

                @foreach(range(1, $paginator->total) as $number)
                    @if($paginator->current === $number)
                        <span><strong>{{ $number }}</strong></span>
                    @else
{{--                        <x-link :href="$page->url">{{ $number }}</x-link>--}}
                        <a href="page-{{ $number }}.html">{{ $number }}</a> <!-- fixme support pretty urls -->
                    @endif
                @endforeach

                @if($paginator->next)
                        <a href="page-{{ $paginator->next }}.html">Next</a> <!-- fixme support pretty urls -->
                @endif
            </nav>
        </div>
    </main>
@endsection
