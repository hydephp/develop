<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Blogging\DynamicPages;

use Hyde\Pages\InMemoryPage;
use Hyde\Framework\Features\Blogging\Models\PostAuthor;
use Hyde\Framework\Features\Blogging\DynamicBlogPostPageHelper;

/**
 * @experimental
 *
 * @codeCoverageIgnore This class is still experimental and not yet covered by tests.
 */
class PostAuthorPage extends InMemoryPage
{
    protected PostAuthor $author;

    public function __construct(PostAuthor $author)
    {
        parent::__construct(DynamicBlogPostPageHelper::authorBaseRouteKey()."/$author->username");
    }

    public function getBladeView(): string
    {
        // Todo: Support/document overriding the view

        return 'hyde::pages.author';
    }
}
