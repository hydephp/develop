@php
    /** @var \Hyde\Pages\MarkdownPost $page  */
    /** @var \Hyde\Framework\Features\Blogging\Models\FeaturedImage $image  */
    $image = $page->image;
@endphp
<figure aria-label="Cover image" itemprop="image" itemscope itemtype="http://schema.org/ImageObject" role="doc-cover">
    <img src="{{ $image->getSource() }}" alt="{{ $image->getAltText() ?? '' }}" title="{{ $image->getTitleText() ?? '' }}"
         itemprop="image" class="mb-0">
    <figcaption aria-label="Image caption" itemprop="caption">
        @if($image->hasAuthorName())
            <span>Image by</span>
            <span itemprop="creator" itemscope="" itemtype="http://schema.org/Person">
                @if($image->hasAuthorUrl())
                    <a href="{{ $image->getAuthorUrl() }}" rel="author noopener nofollow" itemprop="url">
                        <span itemprop="name">{{ $image->getAuthorName() }}</span>.
                    </a>
                @else
                    <span itemprop="name">{{ $image->getAuthorName() }}</span>.
                @endif
            </span>
        @endif

        @if($image->hasCopyrightText())
            <span itemprop="copyrightNotice">{{ $image->getCopyrightText() }}</span>.
        @endif

        @if($image->hasLicenseName())
            <span>License</span>
            @if($image->hasLicenseUrl())
                <a href="{{ $image->getLicenseUrl() }}" rel="license nofollow noopener" itemprop="license">{{ $image->getLicenseName() }}</a>.
            @else
                <span itemprop="license">{{ $image->getLicenseName() }}</span>.
            @endif
        @endif
    </figcaption>

    @foreach ($image->getMetadataArray() as $name => $value)
        <meta itemprop="{{ $name }}" content="{{ $value }}">
    @endforeach
</figure> 
