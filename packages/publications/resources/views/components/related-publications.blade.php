@if (count($relatedPublications) > 0)
    <section {{ $attributes->merge(['class' => 'prose dark:prose-invert']) }}>
        <h2>{{ $title }}</h2>
        <nav aria-label="related">
            <ul>
                @foreach ($relatedPublications as $publication)
                    @php
                        $date = \Carbon\Carbon::parse($publication->matter->__createdAt)->format('Y-m-d');
                    @endphp
                    <li>
                        <a href="{{ $publication->getRoute() }}">
                            {{ $publication->title }}
                        </a>
                        ({{ $date }})
                    </li>
                @endforeach
            </ul>
        </nav>
    </section>
@endif