<?php

declare(strict_types=1);

namespace Hyde\Pages\Concerns;

use function basename;
use function unslash;

/**
 * @internal This trait is currently experimental and should not be relied upon outside of Hyde.
 *
 * This trait is used to flatten the output path of a page. This is only used for the documentation pages,
 * where all pages are output to the same directory, but where putting the page in a subdirectory will
 * create a nested navigation structure in the sidebar.
 *
 * @see https://hydephp.com/docs/master/documentation-pages#using-subdirectories
 */
trait UsesFlattenedOutputPaths
{
    /**
     * Get the route key for the page.
     *
     * Uses the identifier basename so nested pages are flattened.
     */
    public function getRouteKey(): string
    {
        return unslash(static::outputDirectory().'/'.basename($this->identifier));
    }

    /**
     * Get the path where the compiled page will be saved.
     *
     * Uses the identifier basename so nested pages are flattened.
     */
    public function getOutputPath(): string
    {
        return static::outputPath(basename($this->identifier));
    }
}
