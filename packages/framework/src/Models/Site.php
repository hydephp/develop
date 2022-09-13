<?php

namespace Hyde\Framework\Models;

/**
 * @see \Hyde\Framework\Testing\Models\SiteTest
 */
final class Site
{
    public ?string $url;
    public ?string $name;
    public ?string $language;

    public function __construct()
    {
        $this->url = self::url();
        $this->name = self::name();
        $this->language = self::language();
    }

    public static function url(): ?string
    {
        return config('site.url');
    }

    public static function name(): ?string
    {
        return config('site.name');
    }

    public static function language(): ?string
    {
        return config('site.language');
    }
}
