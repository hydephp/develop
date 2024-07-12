@props([/** @var \Illuminate\Support\Collection<\Hyde\Framework\Features\Blogging\Models\PostAuthor> */ 'authors'])
@use('Illuminate\Support\Str')
@extends('hyde::layouts.app')
@section('content')

    <main id="content" class="mx-auto max-w-7xl py-16 px-8">
        <h1 class="text-3xl font-bold mb-8 text-center">Our Authors</h1>

        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
            @foreach($authors as $author)
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <div class="flex flex-col items-center">
                        @if($author->avatar)
                            <img src="{{ Hyde::asset($author->avatar) }}" alt="{{ $author->name }}" class="w-24 h-24 rounded-full mb-4">
                        @endif
                        <h2 class="text-xl font-semibold mb-2">{{ $author->name }}</h2>
                        @if($author->bio)
                            <p class="text-gray-600 text-sm mb-4 text-center">{{ Str::limit($author->bio, 100) }}</p>
                        @endif
                        <a href="{{ Hyde::formatLink(\Hyde\Framework\Features\Blogging\DynamicBlogPostPageHelper::authorBaseRouteKey()."/$author->username") }}" class="text-blue-600 hover:underline">View Profile</a>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-gray-500">{{ $author->getPosts()->count() }} {{ Str::plural('post', $author->getPosts()->count()) }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </main>

@endsection