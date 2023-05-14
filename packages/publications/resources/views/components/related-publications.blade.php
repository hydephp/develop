@if (count($relatedPublications) > 0)
    <section {{ $attributes->merge(['class' => 'prose dark:prose-invert']) }}>
        <h2>{{ $title }}</h2>
        <nav aria-label="related">
            <ul>
                @foreach ($relatedPublications as $publication)
                    @php $carbon = \Carbon\Carbon::parse($publication->matter->__createdAt); @endphp
                    <li>
                        <a href="{{ $publication->getRoute() }}">{{ $publication->title }}</a>
                        <time datetime="{{ $carbon }}">({{ $carbon->format('Y-m-d') }})</time>
                    </li>
                @endforeach
            </ul>
        </nav>
    </section>
@endif