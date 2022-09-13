<?php

namespace Hyde\Framework\Models;

/**
 * @see \Hyde\Framework\Testing\Models\SiteTest
 */
class Site
{
    public static function getBaseUrl(): ?string
    {
        return config('site.url');
    }
}
