<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Blogging\DynamicPages;

use Hyde\Pages\InMemoryPage;
use Hyde\Support\Models\Route;
use Hyde\Support\Models\RouteKey;
use Illuminate\Support\Collection;
use Hyde\Foundation\Facades\Routes;

/**
 * @experimental
 *
 * @see \Hyde\Framework\Features\Blogging\BlogPostAuthorPages Which generates these pages.
 * @see \Hyde\Framework\Features\Blogging\DynamicPages\PostAuthorsPage For the index page of all authors.
 */
class PostAuthorsPage extends InMemoryPage
{
    /** @var \Illuminate\Support\Collection<\Hyde\Framework\Features\Blogging\Models\PostAuthor> */
    protected Collection $authors;

    public static string $sourceDirectory = 'authors';
    public static string $outputDirectory = 'authors';
    public static string $layout = 'hyde::pages.authors';
    public static bool $showInNavigation = false;

    public function __construct(Collection $authors)
    {
        parent::__construct('index', [
            'authors' => $authors,
            'navigation' => [
                'visible' => static::$showInNavigation,
            ],
        ]);

        $this->authors = $authors;
    }

    public function getBladeView(): string
    {
        return static::$layout;
    }

    public static function route(): ?Route
    {
        return Routes::get(RouteKey::fromPage(static::class, 'index')->get());
    }
}
