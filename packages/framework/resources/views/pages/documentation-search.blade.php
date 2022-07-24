@php
    $page = new \Hyde\Framework\Models\Pages\DocumentationPage([], '', 'Search', 'search');
    $title = 'Search';
    $currentPage = $page->getCurrentPagePath();
    $markdown = '';
@endphp

@extends('hyde::layouts.docs')
@section('content')
    <h1>Search the documentation site</h1>
    <style>#searchMenuButton {
            display: none !important;
        }

        #search-results {
            max-height: unset !important;
        }</style>
    <x-hyde::docs.search-input class="max-w-xs border-b-4 border-indigo-400" />
@endsection
