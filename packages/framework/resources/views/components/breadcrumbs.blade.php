@if (count($breadcrumbs) > 1)
    <nav aria-label="breadcrumb">
        <ol>
            @foreach ($breadcrumbs as $path=>$title)
                @if (!$loop->last)
                    <a href="{{ $path }}" class="hover:underline">{{ $title }}</a>
                @else
                    <a href="{{ $path }}" aria-current="page">{{ $title }}</a>
                @endif

                @if (!$loop->last)
                    &gt;
                @endif
            @endforeach
        </ol>
    </nav>
@endif
