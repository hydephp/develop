@extends('hyde::layouts.app')
@section('content')
@php($title = "Dashboard")

<main class="mx-auto max-w-7xl py-16 px-8">
	<header class="text-center prose dark:prose-invert mx-auto">
		<h1 class="text-3xl font-bold">Project Dashboard</h1>
		<p>
			<strong>
				Here you can get a quick overview of your project.
			</strong>
		</p>
		<p>
			While this is useful when developing locally,
			you may not want to use it when compiling
			for production.
		</p>
	</header>
</main>

@endsection
