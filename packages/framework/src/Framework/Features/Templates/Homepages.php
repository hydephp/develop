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

    public static function get(string $page): ?PublishableContract
    {
        return self::options()->get($page);
    }

    public static function exists(string $page): bool
    {
        return self::options()->has($page);
    }

    public static function blank(): Homepages\BlankHomepageTemplate
    {
        return new Homepages\BlankHomepageTemplate;
    }

    public static function posts(): Homepages\PostsFeedHomepageTemplate
    {
        return new Homepages\PostsFeedHomepageTemplate;
    }

    public static function welcome(): Homepages\WelcomeHomepageTemplate
    {
        return new Homepages\WelcomeHomepageTemplate;
    }
}
