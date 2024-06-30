@extends('hyde::layouts.docs')
@section('content')
    <h1>Search the documentation site</h1>
    <style>#search-menu-button, .edit-page-link {
            display: none !important;
        }

        #search-results {
            max-height: unset !important;
        }</style>
    <x-hyde::docs.search-input class="max-w-xs border-b-4 border-indigo-400"/>
@endsection