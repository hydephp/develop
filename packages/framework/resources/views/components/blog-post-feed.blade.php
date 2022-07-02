@foreach(\Hyde\Framework\Models\Pages\MarkdownPost::getLatestPosts() as $post)
    @include('hyde::components.article-excerpt')
@endforeach