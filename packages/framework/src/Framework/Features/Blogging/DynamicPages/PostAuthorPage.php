<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Blogging\DynamicPages;

use Hyde\Pages\InMemoryPage;
use Hyde\Framework\Features\Blogging\Models\PostAuthor;

/**
 * @experimental
 */
class PostAuthorPage extends InMemoryPage
{
    protected PostAuthor $author;

    public function __construct(PostAuthor $author)
    {
        parent::__construct("author/$author->username");
    }
}
