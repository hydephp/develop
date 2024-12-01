@props(['items'])

<ul class="table-of-contents py-3 space-y-1.5">
    @foreach($items as $item)
        <li>
            <a href="#{{ $item['slug'] }}" class="block pl-8 -ml-8 opacity-80 hover:opacity-100 hover:bg-gray-200/20 transition-all duration-300 relative">
                <span class="text-[75%] opacity-50 mr-1 hover:opacity-100 transition-opacity duration-300">#</span>
                {{ $item['title'] }}
            </a>
            
            @if(! empty($item['children']))
                <x-hyde::docs.table-of-contents :items="$item['children']" />
            @endif
        </li>
    @endforeach
</ul>