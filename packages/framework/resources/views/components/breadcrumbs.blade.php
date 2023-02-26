@if (count($breadcrumbs) > 1)
    <div class="">
        @foreach ($breadcrumbs as $path=>$title)
            @if (!$loop->last)
                <a href="{{ $path }}" class="hover:underline">{{ $title }}</a>
            @else
                {{ $title }}
            @endif

            @if (!$loop->last)
                &nbsp;&gt;&gt;&nbsp;
            @endif
        @endforeach
    </div>
@endif
