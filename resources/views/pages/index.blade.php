@extends('layouts.app')
@section('content')

<header class="w-screen pb-20 pt-32 lg:pt-44 flex flex-col text-center items-center justify-center min-h-[75vh]">
	<h1 class="text-6xl md:text-7xl text-gray-800 font-black leading-7 md:leading-10">
		HydePHP
	</h1>
	<div class="max-w-3xl p-8 mt-4">
		<strong role="doc-subtitle" class="text-4xl leading-8">
			Static <em>Blog and Documentation-Aware</em> Site Generator
			built on top of the Laravel Zero Framework. 
		</strong>
	</div>
	<div class="my-4">  {{-- Buttons based on https://tailwindcomponents.com/component/tailwind-css-buttons --}}
		<a href="#posts" class="transition duration-300 ease-in-out focus:outline-none focus:ring-4 focus:ring-purple-900 focus:ring-opacity-75 focus:shadow-outline border border-purple-700 hover:bg-purple-700 text-purple-700 hover:text-white font-normal py-2 px-4 m-2 rounded">Latest Posts</a>
		<a href="docs/index.html" class="transition duration-300 ease-in-out focus:outline-none focus:ring-4 focus:ring-purple-900 focus:ring-opacity-75 focus:shadow-outline bg-purple-700 hover:bg-purple-900 text-white font-normal py-2 px-4 m-2 rounded">Documentation</a>
	</div>
</header>

<section id="posts" class="mx-auto max-w-7xl py-16 px-8 lg:mt-8">
    <header class="lg:mb-12 xl:mb-16">
        <h2
			class="text-3xl text-left opacity-75 leading-10 tracking-tight font-extrabold sm:leading-none mb-8 md:mb-12 md:text-4xl md:text-center lg:text-5xl">
			Latest Posts</h2>
    </header>

    <div class="max-w-xl mx-auto">
        @foreach(\App\Hyde\Models\MarkdownPost::getCollection() as $post)
        @include('components.article-excerpt')
        @endforeach
    </div>
</section>

<footer class="py-4 px-6 w-full bg-slate-100 text-center">
	<div class="prose text-center mx-auto">
		Site built with <a href="https://github.com/hydephp/hyde">HydePHP</a>.
		Source code on <a href="https://github.com/hydephp/docs">GitHub</a>.
		License <a href="https://github.com/hydephp/hyde/blob/master/LICENSE.md" rel="license">MIT</a>.
	</div>
</footer>

<style> html, body { scroll-behavior: smooth; } </style>

@endsection