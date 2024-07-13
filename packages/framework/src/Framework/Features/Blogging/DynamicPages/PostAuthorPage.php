<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Blogging\DynamicPages;

use Hyde\Pages\InMemoryPage;
use Hyde\Support\Models\Route;
use Hyde\Support\Models\RouteKey;
use Hyde\Foundation\Facades\Routes;
use Hyde\Framework\Features\Blogging\Models\PostAuthor;

use function compact;

/**
 * @experimental
 *
 * @see \Hyde\Framework\Features\Blogging\BlogPostAuthorPages Which generates these pages.
 * @see \Hyde\Framework\Features\Blogging\DynamicPages\PostAuthorPage For the individual author pages.
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

    public static function route(string $username): ?Route
    {
        return Routes::get(RouteKey::fromPage(static::class, $username)->get());
    }
}
