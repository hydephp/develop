<ol itemscope itemtype="https://schema.org/ItemList">
    @foreach(MarkdownPost::getLatestPosts() as $post)
        <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" class="mt-4 mb-8">
            <meta itemprop="position" content="{{ $loop->iteration }}">
            @include('hyde::components.article-excerpt')
        </li>
    @endforeach
</ol>