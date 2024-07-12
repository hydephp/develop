@props([/** @var \Illuminate\Support\Collection<\Hyde\Framework\Features\Blogging\Models\PostAuthor> */ 'authors'])
@use('Illuminate\Support\Str')
@extends('hyde::layouts.app')
@section('content')

    <main id="content" class="mx-auto max-w-7xl py-16 px-8">
        <h1 class="text-3xl font-bold mb-8 text-center text-gray-900 dark:text-white">Our Authors</h1>

        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
            @foreach($authors as $author)
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md" itemscope itemtype="https://schema.org/Person">
                    <div class="flex flex-col items-center">
                        @if($author->avatar)
                            <img src="{{ Hyde::asset($author->avatar) }}" alt="{{ $author->name }}" class="w-24 h-24 rounded-full mb-4" itemprop="image">
                        @endif
                        <h2 class="text-xl font-semibold mb-2 text-gray-900 dark:text-white" itemprop="name">{{ $author->name }}</h2>
                        @if($author->bio)
                            <p class="text-gray-600 dark:text-gray-300 text-sm mb-4 text-center" itemprop="description">{{ Str::limit($author->bio, 100) }}</p>
                        @endif
                        <a href="{{ Hyde::formatLink(\Hyde\Framework\Features\Blogging\DynamicBlogPostPageHelper::authorBaseRouteKey()."/$author->username") }}" class="text-blue-600 dark:text-blue-400 hover:underline" itemprop="url">View Profile</a>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            <span itemprop="jobTitle">Author</span> •
                            <span itemprop="numberOfItems">{{ $author->getPosts()->count() }}</span> {{ Str::plural('post', $author->getPosts()->count()) }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </main>

@endsection