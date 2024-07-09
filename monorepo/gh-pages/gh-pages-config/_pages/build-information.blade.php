@extends('hyde::layouts.app')
@section('content')
@php($title = "Build Information")

<main class="mx-auto max-w-7xl py-16 px-8">
	<h1 class="text-center text-3xl font-bold">Build Information</h1>
	<p class="text-center">
		<br>
		This page serves a dual purpose: it is an example of a Blade page;
		<br>
		and it shows useful information of the current build.
		<br>
	</p>
	<br>
	<article class="prose mx-auto">
		<pre><code>{{ shell_exec('php hyde debug') }}

Additional information:

PHP Version: {{ PHP_VERSION }} ({{ PHP_SAPI }})
Runner OS: {{ php_uname() }} ({{ PHP_OS }})
Current Timestamp: {{ now()->format('Y-m-d H:i:s') }} {{ now()->format('e') }} (UNIX: {{ time() }})

Hyde app.css SHA1: {{ sha1_file(Hyde::path('_media/app.css')) }}

Current runner git branch: {{ trim(@shell_exec('git rev-parse --abbrev-ref HEAD')) }}
Current runner git commit: {{ trim(@shell_exec('git rev-parse HEAD')) }}
Current runner git commit date: {{ trim(@shell_exec('git show -s --format=%ci HEAD')) }}

Commit reference provided by CI (if any): {{ file_exists(Hyde::path('origin-ref')) ? trim(file_get_contents(Hyde::path('origin-ref'))) : 'none' }}
</code></pre>
	</article>
</main>

@endsection
