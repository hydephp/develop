<?php

namespace Hyde\Framework\Contracts;

use Illuminate\Support\Collection;

interface PageContract
{
    /**
     * Get the directory in where source files are stored.
     * @return string Path relative to the root of the project
     */
    public static function getSourceDirectory(): string;

    /**
     * Get the output subdirectory to store compiled HTML.
     * @return string Relative to the site output directory.
     */
    public static function getOutputDirectory(): string;

    /**
     * Get the file extension of the source files.
     * @return string (e.g. ".md")
     */
    public static function getFileExtension(): string;

    /**
     * Get the class that parses source files into page models.
     * @return string<\Hyde\Framework\Contracts\PageParserContract>
     */
    public static function getParserClass(): string;

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
     * @param  string  $slug
     * @return \Hyde\Framework\Contracts\AbstractPage
     *
     * @see \Hyde\Framework\Testing\Unit\PageModelParseHelperTest
     */
    public static function parse(string $slug): AbstractPage;
}
