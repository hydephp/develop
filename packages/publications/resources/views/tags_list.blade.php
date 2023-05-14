@extends('hyde::layouts.app')
@section('content')
    <main id="content" class="mx-auto max-w-7xl py-16 px-8">
        <div class="prose dark:prose-invert">
            <h1>Publication tags</h1>

            <ol>
                @foreach($tags as $tag => $count)
                    <li>
                        <x-link :href="Routes::get('tags/'.$tag)">
                            <span>{{ $tag }}</span>
                            <small>({{ $count }})</small>
                        </x-link>
                    </li>
                @endforeach
            </ol>
        </div>
    </main>
@endsection
