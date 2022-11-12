<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Templates;

final class Homepages
{
    public static function options(): array
    {
        return [
            'blank' => self::blank(),
            'posts' => self::posts(),
            'welcome' => self::welcome(),
        ];
    }

    public static function get(string $page): ?string
    {
        return self::options()[$page] ?? null;
    }

    public static function exists(string $page): bool
    {
        return array_key_exists($page, self::options());
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
