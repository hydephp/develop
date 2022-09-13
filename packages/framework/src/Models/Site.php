<?php

namespace Hyde\Framework\Models;

/**
 * @see \Hyde\Framework\Testing\Models\SiteTest
 */
class Site
{
    public ?string $name;
    public ?string $url;
    public ?string $language;

    public function __construct()
    {
        $this->name = static::name();
        $this->url = static::url();
        $this->language = static::language();
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
