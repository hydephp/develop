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
            'welcome' => new class extends PublishableView {
                protected static string $title = 'Welcome';
                protected static string $desc = 'The default welcome page.';
                protected static string $path = 'resources/views/homepages/welcome.blade.php';
                protected static ?string $outputPath = 'index.blade.php';
            },
            'posts' => new class extends PublishableView {
                protected static string $title = 'Posts Feed';
                protected static string $desc = 'A feed of your latest posts. Perfect for a blog site!';
                protected static string $path = 'resources/views/homepages/post-feed.blade.php';
                protected static ?string $outputPath = 'index.blade.php';
            },
            'blank' => new class extends PublishableView {
                protected static string $title = 'Blank Starter';
                protected static string $desc = 'A blank Blade template with just the base layout.';
                protected static string $path = 'resources/views/homepages/blank.blade.php';
                protected static ?string $outputPath = 'index.blade.php';
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
