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
        return new Homepages\BlankHomepageTemplate;
    }

    public static function posts(): PublishableContract
    {
        return new Homepages\PostsFeedHomepageTemplate;
    }

    public static function welcome(): PublishableContract
    {
        return new Homepages\WelcomeHomepageTemplate;
    }
}
