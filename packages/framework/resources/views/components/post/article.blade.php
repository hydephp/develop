<article aria-label="Article" id="{{ $page->identifier }}" itemscope
         itemtype="https://schema.org/Article"
    @class(['post-article mx-auto prose dark:prose-invert', 'torchlight-enabled' => Hyde\Facades\Features::hasTorchlight()])>
    <meta itemprop="identifier" content="{{ $page->identifier }}">
    @if($page->canonicalUrl !== null)
        <meta itemprop="url" content="{{ $page->canonicalUrl }}">
    @endif

    <header aria-label="Header section" role="doc-pageheader">
        <h1 itemprop="headline" class="mb-4">{{ $page->title }}</h1>
        <div id="byline" aria-label="About the post" role="doc-introduction">
            @includeWhen(isset($page->date), 'hyde::components.post.date')
            @includeWhen(isset($page->author), 'hyde::components.post.author')
            @includeWhen(isset($page->category), 'hyde::components.post.category')
        </div>
    </header>
    @includeWhen(isset($page->image), 'hyde::components.post.image')
    <div aria-label="Article body" itemprop="articleBody">
        {{ $content }}
    </div>
    <span class="sr-only">End of article</span>
</article>