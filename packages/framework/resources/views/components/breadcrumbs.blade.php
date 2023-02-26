@if (count($breadcrumbs) > 1)
    <nav aria-label="breadcrumb">
        <ol class="flex">
            @foreach ($breadcrumbs as $path => $title)
                <li>
                    @if (! $loop->last)
                        <a href="{{ $path }}" class="hover:underline">{{ $title }}</a>
                    @else
                        <a href="{{ $path }}" aria-current="page">{{ $title }}</a>
                    @endif
                </li>

                @if (! $loop->last)
                    <span>&gt;</span>
                @endif
            @endforeach
        </ol>
    </nav>
@endif
