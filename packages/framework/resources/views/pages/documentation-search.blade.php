@extends('hyde::layouts.docs')
@section('content')
    <h1>Search the documentation site</h1>
    <style>#search-menu-button, .edit-page-link { display: none !important; }</style>

    <div class="not-prose">
        <x-hyde::docs.hyde-search class="max-w-sm" :modal="false" />
    </div>
@endsection
