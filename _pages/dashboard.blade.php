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

	<section class="prose dark:prose-invert mx-auto mt-8">
		<h2>Blade Pages</h2>
		<table>
			<thead>
				<tr>
					<th>Name</th>
					<th>Path</th>
				</tr>
			</thead>
			<tbody>
				@foreach (\Hyde\Framework\Models\BladePage::all() as $page)
				<tr>
					<td>{{ $page->view }}</td>
					<td>{{ $page->slug }}</td>
				</tr>
				@endforeach		
			</tbody>
		</table>
	</section>

	<section class="prose dark:prose-invert mx-auto mt-8">
		<h2>Markdown Pages</h2>
		<table>
			<thead>
				<tr>
					<th>Name</th>
					<th>Path</th>
				</tr>
			</thead>
			<tbody>
				@foreach (\Hyde\Framework\Models\MarkdownPage::all() as $page)
				<tr>
					<td>{{ $page->title }}</td>
					<td>{{ $page->slug }}</td>
				</tr>
				@endforeach		
			</tbody>
		</table>
	</section>

	<section class="prose dark:prose-invert mx-auto mt-8">
		<h2>Documentation Pages</h2>
		<table>
			<thead>
				<tr>
					<th>Name</th>
					<th>Path</th>
				</tr>
			</thead>
			<tbody>
				@foreach (\Hyde\Framework\Models\DocumentationPage::all() as $page)
				<tr>
					<td>{{ $page->title }}</td>
					<td>{{ $page->slug }}</td>
				</tr>
				@endforeach		
			</tbody>
		</table>
	</section>
</main>

@endsection
