@props([/** @var \Illuminate\Support\Collection<\Hyde\Framework\Features\Blogging\Models\PostAuthor> */ 'authors'])
@use('Illuminate\Support\Str')
@extends('hyde::layouts.app')
@section('content')
    @php
        // If any author has an avatar, we'll use avatars for all authors, so the layout looks consistent.
        $usesAvatars = $authors->contains(fn ($author) => $author->avatar);
        // The avatar fallback can be changed here (and it also works if a set avatar can't be loaded).
        $avatarFallback = Asset::hasMediaFile('avatar.png') ? Asset::mediaLink('avatar.png') : 'https://cdn.jsdelivr.net/gh/hydephp/cdn-static@master/avatar.png';
    @endphp

    <main id="content" class="mx-auto max-w-7xl py-16 px-8">
        <h1 class="text-3xl font-bold mb-8 text-center text-gray-900 dark:text-white">Our Authors</h1>

        <div class="flex flex-wrap justify-center gap-8">
            @foreach($authors as $author)
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md w-full md:w-1/2 lg:w-1/3" itemscope itemtype="https://schema.org/Person">
                    <div class="flex flex-col items-center">
                        @if($usesAvatars)
                            <img src="{{ asset($author->avatar ?? $avatarFallback) }}" alt="{{ $author->name }}" class="w-24 h-24 rounded-full mb-4" itemprop="image" onerror="this.onerror=null; this.src='{{ $avatarFallback }}'">
                        @endif
                        <h2 class="text-xl font-semibold mb-2 text-gray-900 dark:text-white" itemprop="name">{{ $author->name }}</h2>
                        @if($author->bio)
                            <p class="text-gray-600 dark:text-gray-300 text-sm mb-4 text-center" itemprop="description">{{ Str::limit($author->bio, 100) }}</p>
                        @endif
                        <a href="{{ route(\Hyde\Framework\Features\Blogging\BlogPostAuthorPages::authorBaseRouteKey()."/$author->username") }}" class="text-blue-600 dark:text-blue-400 hover:underline" itemprop="url">View Profile</a>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $author->getPosts()->count() }} {{ Str::plural('post', $author->getPosts()->count()) }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </main>

@endsection