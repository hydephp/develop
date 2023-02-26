@if (count($breadcrumbs) > 1)
    <nav aria-label="breadcrumb">
        @foreach ($breadcrumbs as $path=>$title)
            @if (!$loop->last)
                <a href="{{ $path }}" class="hover:underline">{{ $title }}</a>
            @else
                <span aria-current="page">{{ $title }}</span>
            @endif

            @if (!$loop->last)
                &gt;
            @endif
        @endforeach
    </nav>
@endif
