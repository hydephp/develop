<?php

namespace Hyde\Framework\Models;

/**
 * @see \Hyde\Framework\Testing\Models\SiteTest
 */
class Site
{
    public static function name(): ?string
    {
        return config('site.name');
    }

    public static function url(): ?string
    {
        return config('site.url');
    }
}
