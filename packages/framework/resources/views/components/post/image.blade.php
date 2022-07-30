<figure aria-label="Cover image" itemprop="image" itemscope itemtype="http://schema.org/ImageObject" role="doc-cover">
    <img src="{{ $page->image->getLink() }}" alt="{{ $page->image->description ?? '' }}" title="{{ $page->image->title ?? '' }}" itemprop="image" class="mb-0"> 
    <figcaption aria-label="Image caption" itemprop="caption"> 
        {!! $page->image->getFluentAttribution() !!} 
    </figcaption> 
    @foreach ($page->image->getMetadataArray() as $name => $value) 
	<meta itemprop="{{ $name }}" content="{{ $value }}"> 
    @endforeach 
</figure> 
