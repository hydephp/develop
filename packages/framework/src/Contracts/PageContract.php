<?php

namespace Hyde\Framework\Contracts;

use Illuminate\Support\Collection;

/**
 * Defines the requirements for a Page Model to be used and usable by the Hyde Framework.
 *
 * A Page Model is a class that contains the data for a single page,
 * as well as class-wide information for Hyde about how to find,
 * parse, process, render, and compile, and store the page.
 *
 * Note that in addition, certain static class properties are required.
 * These are defined in the AbstractPage class, Therefore, all Page Models
 * must extend that class, which in turn implements this interface.
 */
interface PageContract
{
    /**
     * Get a collection of all pages, parsed into page models.
     *
     * @return \Illuminate\Support\Collection<\Hyde\Framework\Contracts\PageContract>
     *
     * @see \Hyde\Framework\Testing\Unit\PageModelGetHelperTest
     */
    public static function all(): Collection;

    /**
     * Get an array of all the source file slugs for the model.
     * Essentially an alias of CollectionService::getAbstractPageList().
     *
     * @return array<string>
     *
     * @see \Hyde\Framework\Testing\Unit\PageModelGetAllFilesHelperTest
     */
    public static function files(): array;

    /**
     * Parse a source file slug into a page model.
     *
     * @param  string  $slug Base name of the source file, without the file extension or the source directory.
     * @return \Hyde\Framework\Contracts\AbstractPage
     *
     * @see \Hyde\Framework\Testing\Unit\PageModelParseHelperTest
     */
    public static function parse(string $slug): AbstractPage;
}
