<article aria-label="Article" id="{{ Hyde::qualifiedUrl("posts/$page->slug", '') }}" itemscope itemtype="http://schema.org/Article"
    @class(['post-article mx-auto prose dark:prose-invert', 'torchlight-enabled' => Hyde\Framework\Helpers\Features::hasTorchlight()])>
    <meta itemprop="identifier" content="{{ $page->slug }}">
    @if(Hyde::hasSiteUrl())
    <meta itemprop="url" content="{{ Hyde::qualifiedUrl('posts/' . $page->slug) }}">
    @endif
    
    <header aria-label="Header section" role="doc-pageheader">
        <h1 itemprop="headline" class="mb-4">{{ $title ?? 'Blog Post' }}</h1>
		<div id="byline" aria-label="About the post" role="doc-introduction">
            @includeWhen(isset($page->date), 'hyde::components.post.date')
		    @includeWhen(isset($page->author), 'hyde::components.post.author')
            @includeWhen(isset($page->category), 'hyde::components.post.category')
        </div>
    </header>
    @includeWhen(isset($page->image), 'hyde::components.post.image')
    <div aria-label="Article body" itemprop="articleBody">
        {!! $markdown !!}
    </div>
    <span class="sr-only">End of article</span>
</article>
