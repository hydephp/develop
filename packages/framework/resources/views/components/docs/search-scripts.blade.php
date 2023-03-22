<script src="https://cdn.jsdelivr.net/npm/hydesearch@0.2.1/dist/HydeSearch.min.js" defer></script>
<script>
    window.addEventListener('load', function () {
        const searchIndexLocation = '{{ Hyde::relativeLink(ltrim(DocumentationPage::outputDirectory().'/search.json', '/')) }}';
        const Search = new HydeSearch(searchIndexLocation);

        Search.init();
    });
</script>