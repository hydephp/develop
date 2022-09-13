<?php

namespace Hyde\Framework\Models;

/**
 * @see \Hyde\Framework\Testing\Models\SiteTest
 */
final class Site
{
    public ?string $name;
    public ?string $url;
    public ?string $language;

    public function __construct()
    {
        $this->name = self::name();
        $this->url = self::url();
        $this->language = self::language();
    }

    public static function name(): ?string
    {
        return config('site.name');
    }

    public static function url(): ?string
    {
        return config('site.url');
    }

    public static function language(): ?string
    {
        return config('site.language');
    }
}
