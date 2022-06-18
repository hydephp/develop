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
     * @see \Hyde\Testing\Framework\Unit\PageModelGetHelperTest
     */
    public static function all(): Collection;

    /**
     * Get an array of all the source file slugs for the model.
     * Essentially an alias of CollectionService::getAbstractPageList().
     *
     * @return array<string>
     *
     * @see \Hyde\Testing\Framework\Unit\PageModelGetAllFilesHelperTest
     */
    public static function files(): array;

    /**
     * Parse a source file slug into a page model.
     *
     * @param  string  $slug
     * @return \Hyde\Framework\Contracts\AbstractPage
     *
     * @see \Hyde\Testing\Framework\Unit\PageModelParseHelperTest
     */
    public static function parse(string $slug): AbstractPage;
}
