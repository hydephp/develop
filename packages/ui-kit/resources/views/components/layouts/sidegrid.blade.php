@extends('hyde::layouts.app')
@section('content')

    <div class="mx-auto max-w-7xl py-8 px-4 flex">
        <main id="content" class="py-8 px-4 flex-grow">
            {{ $slot }}
        </main>

        <aside id="sidegrid" class="py-8 px-4 w-80">
            {{ $aside }}
        </aside>
    </div>

@endsection
