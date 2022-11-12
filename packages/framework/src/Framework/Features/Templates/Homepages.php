<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Templates;

use Illuminate\Support\Collection;

final class Homepages
{
    public static function options(): Collection
    {
        return new Collection([
            'blank' => self::blank(),
            'posts' => self::posts(),
            'welcome' => self::welcome(),
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

    public static function blank(): PublishableContract
    {
        return new class extends PublishableView
        {
            protected static string $title = 'Blank Starter';
            protected static string $desc = 'A blank Blade template with just the base layout.';
            protected static string $path = 'resources/views/homepages/blank.blade.php';
            protected static ?string $outputPath = 'index.blade.php';
        };
    }

    public static function posts(): PublishableContract
    {
        return new class extends PublishableView
        {
            protected static string $title = 'Posts Feed';
            protected static string $desc = 'A feed of your latest posts. Perfect for a blog site!';
            protected static string $path = 'resources/views/homepages/post-feed.blade.php';
            protected static ?string $outputPath = 'index.blade.php';
        };
    }

    public static function welcome(): PublishableContract
    {
        return new class extends PublishableView
        {
            protected static string $title = 'Welcome';
            protected static string $desc = 'The default welcome page.';
            protected static string $path = 'resources/views/homepages/welcome.blade.php';
            protected static ?string $outputPath = 'index.blade.php';
        };
    }
}
