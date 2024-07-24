@props([/** @var \Hyde\Framework\Features\Blogging\Models\PostAuthor */ 'author'])
@extends('hyde::layouts.app')
@section('content')
    @use('Hyde\Framework\Features\Blogging\DynamicPages\PostAuthorPage')
    @use('Hyde\Framework\Features\Blogging\DynamicPages\PostAuthorsPage')

    <main id="content" class="mx-auto max-w-7xl py-16 px-8" itemscope itemtype="https://schema.org/ProfilePage">
        <nav itemscope itemtype="https://schema.org/BreadcrumbList" class="mb-8">
            <ol class="flex space-x-2">
                <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                    <a itemprop="item" href="{{ route('index')->getUrl() }}">
                        <span itemprop="name">Home</span>
                    </a>
                    <meta itemprop="position" content="1" />
                </li>
                <li>&gt;</li>
                <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                    <a itemprop="item" href="{{ PostAuthorsPage::route()->getUrl() }}">
                        <span itemprop="name">Authors</span>
                    </a>
                    <meta itemprop="position" content="2" />
                </li>
                <li>&gt;</li>
                <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                    <span itemprop="name">{{ $author->name }}</span>
                    <meta itemprop="item" content="{{ PostAuthorPage::route($author->username)->getUrl() }}" />
                    <meta itemprop="position" content="3" />
                </li>
            </ol>
        </nav>

        <div class="flex flex-col items-center" itemprop="mainEntity" itemscope itemtype="https://schema.org/Person">
            @if($author->avatar)
                <img src="{{ asset($author->avatar) }}" alt="{{ $author->name }}" class="w-32 h-32 rounded-full mb-4" itemprop="image">
            @endif
            <h1 class="text-3xl font-bold mb-2 text-gray-900 dark:text-white" itemprop="name">{{ $author->name }}</h1>
            @if($author->bio)
                <p class="text-gray-600 dark:text-gray-300 mb-4" itemprop="description">{{ $author->bio }}</p>
            @endif
            @if($author->website)
                <a href="{{ $author->website }}" class="text-blue-600 dark:text-blue-400 hover:underline mb-4" itemprop="url">Website</a>
            @endif
            @if($author->socials)
                <div class="flex space-x-4 mb-6">
                    @foreach($author->socials as $platform => $handle)
                        <a href="https://{{ $platform }}.com/{{ $handle }}" class="text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white" itemprop="sameAs">
                            {{ ucfirst($platform) }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="mt-12">
            <h2 class="text-2xl font-semibold mb-4 text-gray-900 dark:text-white">Posts by {{ $author->name }}</h2>
            <ul class="space-y-4">
                @foreach($author->getPosts() as $post)
                    <li itemprop="relatedItem" itemscope itemtype="https://schema.org/BlogPosting">
                        @include('hyde::components.article-excerpt', ['post' => $post])
                    </li>
                @endforeach
            </ul>
        </div>
    </main>

@endsection