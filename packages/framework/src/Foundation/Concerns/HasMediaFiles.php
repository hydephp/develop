<?php

declare(strict_types=1);

namespace Hyde\Foundation\Concerns;

/**
 * @internal Single-use trait for the Filesystem class.
 *
 * @see \Hyde\Foundation\Kernel\Filesystem
 */
trait HasMediaFiles
{
    /** @return array<string, \Hyde\Support\Filesystem\MediaFile> The array keys are the filenames relative to the _media/ directory */
    protected array $assets = [];
}
