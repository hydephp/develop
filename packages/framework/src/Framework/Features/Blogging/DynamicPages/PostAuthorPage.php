<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Blogging\DynamicPages;

use Hyde\Pages\InMemoryPage;
use Hyde\Framework\Features\Blogging\Models\PostAuthor;

use function compact;

/**
 * @experimental
 *
 * @codeCoverageIgnore This class is still experimental and not yet covered by tests.
 */
class PostAuthorPage extends InMemoryPage
{
    protected PostAuthor $author;

    public static string $sourceDirectory = 'authors';
    public static string $outputDirectory = 'authors';
    public static string $layout = 'hyde::pages.author';

    public function __construct(PostAuthor $author)
    {
        parent::__construct($author->username, compact('author'));
    }

    public function getBladeView(): string
    {
        return static::$layout;
    }
}
