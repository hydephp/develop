@extends('hyde::layouts.docs')
@section('content')
    @php
        $title = Config::getString('docs.sidebar.header', 'Documentation');

        $searchTitle = str_ends_with(strtolower($title), ' docs')
            ? 'Search the ' . substr($title, 0, -5) . ' Documentation'
            : 'Search ' . $title;
    @endphp
    <h1>{{ $searchTitle }}</h1>
    <style>#search-menu-button, .edit-page-link {
            display: none !important;
        }

        #search-results {
            max-height: unset !important;
        }</style>
    <x-hyde::docs.search-input class="max-w-xs border-b-4 border-indigo-400"/>
@endsection