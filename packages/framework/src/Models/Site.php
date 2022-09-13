<?php

namespace Hyde\Framework\Models;

/**
 * @see \Hyde\Framework\Testing\Models\SiteTest
 */
class Site
{
    public static function url(): ?string
    {
        return config('site.url');
    }
}
