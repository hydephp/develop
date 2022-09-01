<div class="dropdown-container relative">
    <button class="dropdown-button block my-2 md:my-0 md:inline-block py-1 text-gray-700 hover:text-gray-900 dark:text-gray-100">
        {{ $label }}
    </button>
    <ul class="dropdown-items absolute">
        {{ $slot }}
    </ul>
</div>
