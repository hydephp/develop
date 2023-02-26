{{-- The Post Page Layout --}}
@extends('hyde::layouts.app')
@section('content')

<x-hyde::breadcrumbs />

<main id="content" class="mx-auto max-w-7xl py-16 px-8">
	@include('hyde::components.post.article')
</main>

@endsection