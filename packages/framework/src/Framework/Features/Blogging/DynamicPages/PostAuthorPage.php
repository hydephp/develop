<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Blogging\DynamicPages;

use Hyde\Pages\InMemoryPage;
use Hyde\Framework\Features\Blogging\Models\PostAuthor;

use function compact;
use function Hyde\path_join;

/**
 * @experimental
 *
 * @codeCoverageIgnore This class is still experimental and not yet covered by tests.
 */
class PostAuthorPage extends InMemoryPage
{
    protected PostAuthor $author;

    public static string $outputDirectory = 'authors';

    public function __construct(PostAuthor $author)
    {
        $identifier = path_join(static::$outputDirectory, $author->username);

        parent::__construct($identifier, compact('author'));
    }

    public function getBladeView(): string
    {
        // Todo: Support/document overriding the view

        return 'hyde::pages.author';
    }
}
