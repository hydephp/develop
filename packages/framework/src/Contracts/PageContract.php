<?php

namespace Hyde\Framework\Contracts;

use Illuminate\Support\Collection;

interface PageContract
{
    /**
     * Get a collection of all pages, parsed into page models.
     *
     * @return \Illuminate\Support\Collection<\Hyde\Framework\Contracts\PageContract>
     *
     * @see \Tests\Unit\PageModelGetHelperTest
     */
    public static function all(): Collection;
}
