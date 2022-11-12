<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Templates;

use Illuminate\Support\Collection;
use function collect;

final class Homepages
{
    public static function options(): Collection
    {
        return collect([
            'blank' => self::blank(),
            'posts' => self::posts(),
            'welcome' => self::welcome(),
        ]);
    }

    public static function get(string $page): ?string
    {
        return self::options()->get($page);
    }

    public static function exists(string $page): bool
    {
        return self::options()->has($page);
    }

    public static function blank(): string
    {
        return Homepages\BlankHomepageTemplate::class;
    }

    public static function posts(): string
    {
        return Homepages\PostsFeedHomepageTemplate::class;
    }

    public static function welcome(): string
    {
        return Homepages\WelcomeHomepageTemplate::class;
    }
}
