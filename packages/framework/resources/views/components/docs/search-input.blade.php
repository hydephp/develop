<div id="hyde-search" x-data="hydeSearch">
    <noscript>
        The search feature requires JavaScript to be enabled in your browser.
    </noscript>
    
    <div class="relative">
        <input 
            {{ $attributes->merge(['class' => 'w-full rounded text-base leading-normal bg-gray-100 dark:bg-gray-700 py-2 px-3']) }}
            type="search" 
            name="search" 
            id="search-input"
            x-model="searchTerm"
            @input.debounce.250ms="search()"
            placeholder="Search..." 
            autocomplete="off" 
            autofocus
        >
        
        <div x-show="isLoading" class="absolute right-3 top-2.5">
            <div class="animate-spin h-5 w-5 border-2 border-gray-500 rounded-full border-t-transparent"></div>
        </div>
    </div>

    <div x-show="searchTerm" class="mt-4">
        <p x-text="statusMessage" class="text-sm text-gray-600 dark:text-gray-400 mb-4"></p>
        
        <dl class="space-y-4 max-h-[60vh] overflow-y-auto">
            <template x-for="result in results" :key="result.slug">
                <div>
                    <dt class="font-medium">
                        <a :href="result.destination" 
                           x-text="result.title"
                           class="text-blue-600 dark:text-blue-400 hover:underline"></a>
                        <span class="text-sm text-gray-600 dark:text-gray-400" 
                              x-text="`, ${result.matches} occurrence${result.matches !== 1 ? 's' : ''} found.`"></span>
                    </dt>
                    <dd class="mt-1 text-sm text-gray-600 dark:text-gray-400" x-html="result.context"></dd>
                </div>
            </template>
        </dl>
    </div>
</div>