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
                    return;
                }

                const searchResults = this.searchIndex.filter(entry => 
                    entry.title.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                    entry.content.toLowerCase().includes(this.searchTerm.toLowerCase())
                );

                if (searchResults.length === 0) {
                    this.statusMessage = 'No results found.';
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
            },

            getSearchContext(content) {
                const searchTermPos = content.toLowerCase().indexOf(this.searchTerm.toLowerCase());
                const sentenceStart = content.lastIndexOf('.', searchTermPos) + 1;
                const sentenceEnd = content.indexOf('.', searchTermPos) + 1;
                const sentence = content.substring(sentenceStart, sentenceEnd).trim();
                
                return sentence.replace(
                    new RegExp(this.searchTerm, 'gi'),
                    match => `<mark class="bg-yellow-200 dark:bg-yellow-800">${match}</mark>`
                );
            }
        }));
    });
</script>