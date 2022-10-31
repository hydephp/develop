@props([
    /** @var \Hyde\Framework\Features\Documentation\SemanticDocumentationArticle $document */
    'document',
])

<article id="document" itemscope itemtype="https://schema.org/Article" @class([
        'mx-auto lg:ml-8 prose dark:prose-invert max-w-3xl p-12 md:px-16 max-w-[1000px] min-h-[calc(100vh_-_4rem)]',
        'torchlight-enabled' => $document->hasTorchlight()])>
    @yield('content')

    <header id="document-header" class="flex items-center flex-wrap prose-h1:mb-3">
        {{ $document->renderHeader() }}
    </header>
    <section id="document-main-content" itemprop="articleBody">
        {{ $document->renderBody() }}
    </section>
    <footer id="document-footer" class="flex items-center flex-wrap mt-8 prose-p:my-3 justify-between text-[90%]">
        {{ $document->renderFooter() }}
    </footer>
</article>