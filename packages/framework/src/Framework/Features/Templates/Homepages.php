<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Templates;

use Illuminate\Support\Collection;

/**
 * @deprecated May be replaced by vendor:publish in the future.
 *
 * @internal This class is currently experimental and should not be relied upon outside the framework as it may change at any time.
 * @experimental
 */
final class Homepages
{
    public static function options(): Collection
    {
        /** Todo: Unwrap classes to multidimensional array */
        return new Collection([
            'welcome' => new class extends PublishableView
            {
                protected static string $name = 'Welcome';
                protected static string $description = 'The default welcome page.';
                protected static string $group = 'hyde-welcome-page';
            },
            'posts' => new class extends PublishableView
            {
                protected static string $name = 'Posts Feed';
                protected static string $description = 'A feed of your latest posts. Perfect for a blog site!';
                protected static string $group = 'hyde-posts-page';
            },
            'blank' => new class extends PublishableView
            {
                protected static string $name = 'Blank Starter';
                protected static string $description = 'A blank Blade template with just the base layout.';
                protected static string $group = 'hyde-blank-page';
            },
        ]);
    }

    public static function exists(string $page): bool
    {
        return self::options()->has($page);
    }

    public static function get(string $page): ?PublishableContract
    {
        return self::options()->get($page);
    }
}
