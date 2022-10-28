@php
/** @var \Hyde\Framework\Features\Blogging\Models\FeaturedImage $image */
$image = $page->image;
@endphp
<figure aria-label="Cover image" itemprop="image" itemscope itemtype="http://schema.org/ImageObject" role="doc-cover">
    <img src="{{ $image->getLink() }}" alt="{{ $image->description ?? '' }}" title="{{ $image->title ?? '' }}" itemprop="image" class="mb-0">
    <figcaption aria-label="Image caption" itemprop="caption"> 
        {{ $image->getFluentAttribution() }}
    </figcaption> 
    @foreach ($image->getMetadataArray() as $name => $value)
	<meta itemprop="{{ $name }}" content="{{ $value }}"> 
    @endforeach 
</figure> 
