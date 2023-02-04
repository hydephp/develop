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
        return new Collection([
            'welcome'=> [
                'name' => 'Welcome',
                'description' => 'The default welcome page.',
                'group' => 'hyde-welcome-page',
            ],
            'posts'=> [
                'name' => 'Posts Feed',
                'description' => 'A feed of your latest posts. Perfect for a blog site!',
                'group' => 'hyde-posts-page',
            ],
            'blank'=>  [
                'name' => 'Blank Starter',
                'description' => 'A blank Blade template with just the base layout.',
                'group' => 'hyde-blank-page',
            ]
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
