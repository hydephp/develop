<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\XmlGenerators;

interface XmlGeneratorContract
{
    /**
     * Generate a new XML document and get the contents as a string.
     */
    public static function generate(): string;
}
