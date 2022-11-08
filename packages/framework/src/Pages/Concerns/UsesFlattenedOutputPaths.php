<?php

declare(strict_types=1);

namespace Hyde\Pages\Concerns;

use function basename;
use function trim;

/**
 * @internal This trait is currently experimental and should not be relied upon outside of Hyde.
 */
trait UsesFlattenedOutputPaths
{
    /** @inheritDoc */
    public function getRouteKey(): string
    {
        return trim(static::outputDirectory().'/'.basename($this->identifier), '/');
    }

    /**
     * Return the output path for the identifier basename so nested pages are flattened.
     */
    public function getOutputPath(): string
    {
        return static::outputPath(basename($this->identifier));
    }
}
