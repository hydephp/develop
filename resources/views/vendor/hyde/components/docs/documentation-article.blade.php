@php
    $document = \Hyde\Framework\Features\Documentation\SemanticDocumentationArticle::make($page);
@endphp

<article id="document" itemscope itemtype="https://schema.org/Article" @class([
        'mx-auto lg:ml-8 prose dark:prose-invert max-w-3xl p-12 md:px-16 max-w-[1000px] min-h-[calc(100vh_-_4rem)]',
        'torchlight-enabled' => $document->hasTorchlight()])>
    @yield('content')
    <blockquote class="warning" style="max-width: 680px; padding: 10px 20px;">
        <p>
            <strong>WARNING</strong>
            You're browsing the documentation for an upcoming version of HydePHP.
            The documentation and features of this release are subject to change.
        </p>
    </blockquote>

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

<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/highlightjs/cdn-release@11.7.0/build/styles/dark.min.css">
<script src="https://cdn.jsdelivr.net/gh/highlightjs/cdn-release@11.7.0/build/highlight.min.js"></script>
<script>hljs.highlightAll();</script>
<style>.prose :where(pre):not(:where([class~=not-prose] *)) { background-color: #303030 } </style>
<style>pre code.hljs { padding: 0; }</style>
