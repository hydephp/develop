@php
	$page = new \Hyde\Framework\Models\DocumentationPage([], '', 'Search', 'search');
	$title = 'Search';
	$currentPage = config('docs.output_directory', 'docs').'/search';
	$markdown = '';
@endphp

@extends('hyde::layouts.docs')
@section('content')
	<h1>Search the documentation site</h1>
	<style>#searchMenuButton{display:none!important;}#search-results{max-height:unset!important;}</style>
	@include('hyde::components.docs.search-input')
@endsection