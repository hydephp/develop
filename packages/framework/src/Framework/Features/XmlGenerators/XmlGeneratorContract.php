<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\XmlGenerators;

/**
 * Defines the public API for XML generators.
 *
 * @see \Hyde\Framework\Features\XmlGenerators\RssFeedGenerator
 * @see \Hyde\Framework\Features\XmlGenerators\SitemapGenerator
 */
interface XmlGeneratorContract
{
    /**
     * Generate a new XML document and get the contents as a string.
     */
    public static function make(): string;

    /**
     * Create a new XML generator instance.
     */
    public function __construct();

    /**
     * Generate the XML document.
     *
     * @return $this
     */
    public function generate(): static;

    /**
     * Get the XML document as a string.
     */
    public function getXml(): string;
}
