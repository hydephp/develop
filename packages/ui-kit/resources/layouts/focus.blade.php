@extends('hyde::framework.resources.views.layouts.app')
@section('content')

    <main id="content" class="mx-auto max-w-7xl py-16 px-8">
        {{ $slot }}
    </main>

@endsection
