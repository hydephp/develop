by author
<address itemprop="author" itemscope itemtype="https://schema.org/Person" aria-label="The post author" style="display: inline;"> 
	@if($page->author->website)
	<a href="{{ $page->author->website }}" rel="author" itemprop="url" aria-label="The author's website">
	@endif
	<span itemprop="name" aria-label="The author's name" {{ ($page->author->username &&  ($page->author->username !== $page->author->name)) ? 'title=@'. urlencode($page->author->username) .'' : '' }}>{{ $page->author->name ?? $page->author->username }}</span> 
	@if($page->author->website)
	</a>
	@endif
</address> 
