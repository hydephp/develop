@if (count($relatedPublications) > 0)
    <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200 mt-8 mb-2">{{ $title }}</h2>
    <nav {{ $attributes->merge(['aria-label' => 'related']) }} class="text-xl mb-4">
        <ul class="flex pl-6">
            @foreach ($relatedPublications as $publication)
                @php
                    $date = \Carbon\Carbon::parse($publication->matter->__createdAt)->format('Y-m-d');
                @endphp
                <li class="list-disc">
                    <a href="{{ $publication->getRoute() }}" class="text-primary-600 hover:text-primary-400 dark:text-primary-500 dark:hover:text-primary-600">
                        {{ $publication->title }} ({{ $date }})
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
@endif