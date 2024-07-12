@props([/** @var \Hyde\Framework\Features\Blogging\Models\PostAuthor */ 'author'])
@extends('hyde::layouts.app')
@section('content')

    <main id="content" class="mx-auto max-w-7xl py-16 px-8">
        <div class="flex flex-col items-center">
            @if($author->avatar)
                <img src="{{ Hyde::asset($author->avatar) }}" alt="{{ $author->name }}" class="w-32 h-32 rounded-full mb-4">
            @endif
            <h1 class="text-3xl font-bold mb-2">{{ $author->name }}</h1>
            @if($author->bio)
                <p class="text-gray-600 mb-4">{{ $author->bio }}</p>
            @endif
            @if($author->website)
                <a href="{{ $author->website }}" class="text-blue-600 hover:underline mb-4">Website</a>
            @endif
            @if($author->socials)
                <div class="flex space-x-4 mb-6">
                    @foreach($author->socials as $platform => $handle)
                        <a href="https://{{ $platform }}.com/{{ $handle }}" class="text-gray-600 hover:text-gray-800">
                            {{ ucfirst($platform) }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="mt-12">
            <h2 class="text-2xl font-semibold mb-4">Posts by {{ $author->name }}</h2>
            <ul class="space-y-4">
                @foreach($author->getPosts() as $post)
                    <li>
                        <a href="{{ $post->getLink() }}" class="text-lg text-blue-600 hover:underline">{{ $post->title }}</a>
                        <p class="text-sm text-gray-500">{{ $post->date?->short }}</p>
                    </li>
                @endforeach
            </ul>
        </div>
    </main>

@endsection