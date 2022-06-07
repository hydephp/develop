@foreach(\Hyde\Framework\Models\MarkdownPost::getLatestPosts() as $post)
	@include('hyde::components.article-excerpt')
@endforeach