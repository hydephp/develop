@php
    /** @var \Hyde\Pages\MarkdownPost $page  */
    /** @var \Hyde\Framework\Features\Blogging\Models\FeaturedImage $image  */
    $image = $page->image;
@endphp
<figure aria-label="Cover image" itemprop="image" itemscope itemtype="http://schema.org/ImageObject" role="doc-cover">
    <img src="{{ $image->getLink() }}" alt="{{ $image->description ?? '' }}" title="{{ $image->title ?? '' }}" itemprop="image" class="mb-0">
    <figcaption aria-label="Image caption" itemprop="caption">
        @isset($image->author)
            <span>Image by</span>
            <span itemprop="creator" itemscope="" itemtype="http://schema.org/Person">
                @isset($image->attributionUrl)
                    <a href="{{ $image->attributionUrl }}" rel="author noopener nofollow" itemprop="url">
                        <span itemprop="name">{{ $image->author }}</span>.
                    </a>
                @else
                    <span itemprop="name">{{ $image->author }}</span>.
                @endif
            </span>
        @endif

        @isset($image->copyright)
            <span itemprop="copyrightNotice">{{ $image->copyright }}</span>
        @endif

        @isset($image->license)
            <span>License</span>
            @isset($image->licenseUrl)
                <a href="{{ $image->licenseUrl }}" rel="license nofollow noopener" itemprop="license">{{ $image->license }}</a>.
            @else
                <span itemprop="license">{{ $image->license }}</span>.
            @endif
        @endif
    </figcaption>

    @foreach ($image->getMetadataArray() as $name => $value)
        <meta itemprop="{{ $name }}" content="{{ $value }}">
    @endforeach
</figure> 
