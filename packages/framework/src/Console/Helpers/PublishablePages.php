<?php

declare(strict_types=1);

namespace Hyde\Console\Helpers;

/**
 * The registry of publishable starter pages.
 *
 * Ships with Hyde's default catalog (welcome, posts, blank, 404) and serves as an extension point so that
 * Hyde Cloud and plugins can register their own publishable pages via {@see PublishablePages::register()}.
 *
 * @see \Hyde\Console\Helpers\PublishablePage
 */
final class PublishablePages
{
    /** @var array<string, PublishablePage>|null */
    protected static ?array $pages = null;

    /** @return array<string, PublishablePage> */
    public static function all(): array
    {
        return static::$pages ??= static::getDefaultPages();
    }

    public static function get(string $key): ?PublishablePage
    {
        return static::all()[$key] ?? null;
    }

    public static function register(PublishablePage $page): void
    {
        static::$pages = static::all();
        static::$pages[$page->key] = $page;
    }

    /** @internal Primarily used to restore state between tests. */
    public static function clear(): void
    {
        static::$pages = null;
    }

    /** @return array<string, PublishablePage> */
    protected static function getDefaultPages(): array
    {
        return static::keyed([
            new PublishablePage(
                key: 'welcome',
                label: 'Welcome page',
                description: 'The default Hyde welcome page.',
                source: 'resources/views/homepages/welcome.blade.php',
                defaultTarget: '_pages/index.blade.php',
            ),
            new PublishablePage(
                key: 'posts',
                label: 'Posts feed',
                description: 'A feed of your latest posts. Perfect for a blog site!',
                source: 'resources/views/homepages/post-feed.blade.php',
                defaultTarget: '_pages/posts.blade.php',
                alternativeTargets: ['_pages/index.blade.php' => 'Use as your site homepage'],
            ),
            new PublishablePage(
                key: 'blank',
                label: 'Blank page',
                description: 'A blank Blade template with just the base layout.',
                source: 'resources/views/homepages/blank.blade.php',
                defaultTarget: null, // An empty starter you drop anywhere: no default, so its destination is always prompted for (or set via --to).
            ),
            new PublishablePage(
                key: '404',
                label: '404 page',
                description: 'A custom 404 error page.',
                source: 'resources/views/pages/404.blade.php',
                defaultTarget: '_pages/404.blade.php',
                allowCustomTarget: false,
            ),
        ]);
    }

    /**
     * @param  array<PublishablePage>  $pages
     * @return array<string, PublishablePage>
     */
    protected static function keyed(array $pages): array
    {
        return collect($pages)->keyBy(fn (PublishablePage $page): string => $page->key)->all();
    }
}
