<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\GeneratedFiles;

/**
 * @internal Defines the final rendering boundary for generated file pages.
 */
interface GeneratedFileGenerator
{
    /** Generate and return the complete contents to write to the output file. */
    public function generateFile(): string;
}
