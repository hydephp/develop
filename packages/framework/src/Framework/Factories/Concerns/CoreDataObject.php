<?php

declare(strict_types=1);

namespace Hyde\Framework\Factories\Concerns;

/**
 * Experimental class to contain the core data for a page being constructed.
 *
 * It should contain immutable data known at the very start of construction.
 * The data should contain everything needed to identify the unique page.
 */
final class CoreDataObject
{
    public function __construct(
        public readonly string $pageClass,
        public readonly string $identifier,
        public readonly string $sourcePath,
        public readonly string $outputPath,
        public readonly string $routeKey,
    ) {
        //
    }
}
