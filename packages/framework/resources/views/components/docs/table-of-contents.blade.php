@props(['items', 'isChild' => false])

@if(! empty($items))
    <ul class="{{ ! $isChild ? 'table-of-contents pb-3' : 'pl-2' }}">
        @foreach($items as $item)
            <li class="my-[0.35rem]">
                <a href="#{{ $item['slug'] }}" class="block -ml-8 pl-8 opacity-80 hover:opacity-100 hover:bg-gray-200/20 transition-all duration-300 relative">
                    <span class="text-[75%] opacity-50 mr-1 hover:opacity-100 transition-opacity duration-300">#</span>
                    {{ $item['title'] }}
                </a>

                @if(! empty($item['children']))
                    <x-hyde::docs.table-of-contents :items="$item['children']" :isChild="true" />
                @endif
            </li>
        @endforeach
    </ul>
@endif