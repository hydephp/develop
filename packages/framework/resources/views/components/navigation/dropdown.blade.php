<div class="dropdown-container relative" x-data="{ open: false }">
    <button class="dropdown-button block my-2 md:my-0 md:inline-block py-1 text-gray-700 hover:text-gray-900 dark:text-gray-100"
            x-on:click="open = ! open">
        {{ $label }}
    </button>
    <ul class="dropdown-items absolute" :class="open ? '' : 'hidden'">
        {{ $slot }}
    </ul>
</div>
