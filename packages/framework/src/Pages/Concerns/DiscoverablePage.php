<?php

declare(strict_types=1);

namespace Hyde\Pages\Concerns;

use Hyde\Support\Contracts\DiscoverableContract;

/**
 * This class implements the DiscoverableContract interface,
 * and is used by auto-discoverable HydePage classes.
 */
abstract class DiscoverablePage extends HydePage implements DiscoverableContract
{
    /**
     * @var non-empty-string The directory in where source files are stored. Relative to the Hyde root directory.
     */
    protected static string $sourceDirectory;

    /**
     * @var string The output subdirectory to store compiled page HTML. Relative to the _site directory.
     */
    protected static string $outputDirectory;

    /**
     * @var string The file extension of the source files. Normalized to include a leading dot.
     */
    protected static string $fileExtension;
}
