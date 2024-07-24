<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Blogging\DynamicPages;

use Hyde\Pages\InMemoryPage;
use Hyde\Framework\Features\Blogging\Models\PostAuthor;

/**
 * @experimental
 *
 * @see \Hyde\Framework\Features\Blogging\BlogPostAuthorPages Which generates these pages.
 * @see \Hyde\Framework\Features\Blogging\DynamicPages\PostAuthorsPage For the index page of all authors.
 */
class PostAuthorPage extends InMemoryPage
{
    protected PostAuthor $author;

    public static string $sourceDirectory = 'authors';
    public static string $outputDirectory = 'authors';
    public static string $layout = 'hyde::pages.author';

    public function __construct(PostAuthor $author)
    {
        parent::__construct($author->username, [
            'author' => $author,
            'navigation' => [
                'visible' => PostAuthorsPage::$showInNavigation,
            ],
        ]);
    }

    public function getBladeView(): string
    {
        return static::$layout;
    }
}
