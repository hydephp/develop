<?php

declare(strict_types=1);

namespace Hyde\Pages\Concerns;

/**
 * This trait implements the DiscoverableContract interface,
 * and is used by auto-discoverable HydePage classes.
 */
trait Discoverable
{
    protected static string $sourceDirectory;
    protected static string $outputDirectory;
    protected static string $fileExtension;
}
