@props([/** @var \Illuminate\Support\Collection<\Hyde\Framework\Features\Blogging\Models\PostAuthor> */ 'authors'])
@extends('hyde::layouts.app')
@section('content')

    <main id="content" class="mx-auto max-w-7xl py-16 px-8">
        @foreach($authors as $author)
            <div class="mb-8">
                <h2 class="text-3xl font-bold mb-4">{{ $author->name }}</h2>
                <p class="text-gray-600">{{ $author->bio }}</p>
            </div>
        @endforeach
    </main>

@endsection