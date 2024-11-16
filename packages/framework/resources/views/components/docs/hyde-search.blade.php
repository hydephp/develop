<div id="hyde-search" x-data="hydeSearch">
    <div class="relative">
        <input type="search" name="search" id="search-input" x-model="searchTerm" @input="search()" placeholder="Search..." autocomplete="off" autofocus
                {{ $attributes->merge(['class' => 'w-full rounded text-base leading-normal bg-gray-100 dark:bg-gray-700 py-2 px-3']) }}
        >

        <div x-show="isLoading" class="absolute right-3 top-2.5">
            <div class="animate-spin h-5 w-5 border-2 border-gray-500 rounded-full border-t-transparent"></div>
        </div>
    </div>

    <div x-show="searchTerm" class="mt-4">
        <p x-text="statusMessage" class="text-sm text-gray-600 dark:text-gray-400 mb-2 pb-2"></p>

        <dl class="space-y-4 -mt-4 pl-2 -ml-2 max-h-[60vh] overflow-x-hidden overflow-y-auto">
            <template x-for="result in results" :key="result.slug">
                <div>
                    <dt class="font-medium">
                        <a :href="result.destination" x-text="result.title" class="text-indigo-600 dark:text-indigo-400 hover:underline"></a><span class="text-sm text-gray-600 dark:text-gray-400" x-text="`, ${result.matches} occurrence${result.matches !== 1 ? 's' : ''} found.`"></span>
                    </dt>
                    <dd class="mt-1 text-sm text-gray-700 dark:text-gray-300" x-html="result.context"></dd>
                </div>
            </template>
        </dl>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('hydeSearch', () => ({
                searchIndex: [],
                searchTerm: '',
                results: [],
                isLoading: true,
                statusMessage: '',

                async init() {
                    const response = await fetch('{{ Hyde::relativeLink(\Hyde\Framework\Features\Documentation\DocumentationSearchIndex::outputPath()) }}');
                    if (!response.ok) {
                        console.error('Could not load search index');
                        return;
                    }
                    this.searchIndex = await response.json();
                    this.isLoading = false;
                },

                search() {
                    const startTime = performance.now();
                    this.results = [];

                    if (!this.searchTerm) {
                        this.statusMessage = '';
                        window.dispatchEvent(new CustomEvent('search-results-updated', { detail: { hasResults: false } }));
                        return;
                    }

                    const searchResults = this.searchIndex.filter(entry =>
                        entry.title.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                        entry.content.toLowerCase().includes(this.searchTerm.toLowerCase())
                    );

                    if (searchResults.length === 0) {
                        this.statusMessage = 'No results found.';
                        window.dispatchEvent(new CustomEvent('search-results-updated', { detail: { hasResults: false } }));
                        return;
                    }

                    const totalMatches = searchResults.reduce((acc, result) => {
                        return acc + (result.content.match(new RegExp(this.searchTerm, 'gi')) || []).length;
                    }, 0);

                    searchResults.sort((a, b) => {
                        return (b.content.match(new RegExp(this.searchTerm, 'gi')) || []).length
                            - (a.content.match(new RegExp(this.searchTerm, 'gi')) || []).length;
                    });

                    this.results = searchResults.map(result => {
                        const matches = (result.content.match(new RegExp(this.searchTerm, 'gi')) || []).length;
                        const context = this.getSearchContext(result.content);
                        return { ...result, matches, context };
                    });

                    const timeMs = Math.round((performance.now() - startTime) * 100) / 100;
                    this.statusMessage = `Found ${totalMatches} result${totalMatches !== 1 ? 's' : ''} in ${searchResults.length} pages. ~${timeMs}ms`;

                    window.dispatchEvent(new CustomEvent('search-results-updated', { detail: { hasResults: true } }));
                },

                getSearchContext(content) {
                    const searchTermPos = content.toLowerCase().indexOf(this.searchTerm.toLowerCase());
                    const sentenceStart = content.lastIndexOf('.', searchTermPos) + 1;
                    const sentenceEnd = content.indexOf('.', searchTermPos) + 1;
                    const sentence = content.substring(sentenceStart, sentenceEnd).trim();

                    return sentence.replace(
                        new RegExp(this.searchTerm, 'gi'),
                        match => `<mark class="bg-yellow-400 dark:bg-yellow-300">${match}</mark>`
                    );
                }
            }));
        });
    </script>
</div>
