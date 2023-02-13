<div class="dropdown-container relative" x-data="{ open: false }">
    <button class="dropdown-button block my-2 md:my-0 md:inline-block py-1 text-gray-700 hover:text-gray-900 dark:text-gray-100"
            x-on:click="open = ! open">
        {{ $label }}
        <svg class="inline transition-all dark:fill-white" x-bind:style="open ? { transform: 'rotate(180deg)' } : ''" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0z" fill="none"/><path d="M7 10l5 5 5-5z"/></svg>
    </button>
    <div class="dropdown absolute shadow-lg bg-white dark:bg-gray-700 z-50" :class="open ? '' : 'hidden'">
        <ul class="dropdown-items px-3 py-2">
            @isset($items)
                @foreach ($items as $item)
                    <li>
                        @include('hyde::components.navigation.navigation-link')
                    </li>
                @endforeach
            @else
                {{ $slot }}
            @endif
        </ul>
    </div>
</div>
