@if (count($breadcrumbs) > 1)
    <nav {{ $attributes->merge(['aria-label' => 'breadcrumb']) }}>
        <ol class="flex">
            @foreach ($breadcrumbs as $path => $title)
                <li>
                    @if (! $loop->last)
                        <a href="{{ $path }}" class="hover:underline">{{ $title }}</a>
                        <span class="px-1" aria-hidden="true">&gt;</span>
                    @else
                        <a href="{{ $path }}" aria-current="page">{{ $title }}</a>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif