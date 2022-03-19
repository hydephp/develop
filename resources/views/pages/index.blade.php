@extends('layouts.app')
@section('content')

<header class="w-screen min-h-[75vh] flex flex-col text-center items-center justify-center">
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
		<a href="/docs" class="transition duration-300 ease-in-out focus:outline-none focus:ring-4 focus:ring-purple-900 focus:ring-opacity-75 focus:shadow-outline bg-purple-700 hover:bg-purple-900 text-white font-normal py-2 px-4 m-2 rounded">Documentation</a>
	</div>
</header>

@endsection