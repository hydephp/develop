<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Hyde;
use Hyde\Facades\Filesystem;
use Hyde\Framework\Concerns\InteractsWithDirectories;
use Hyde\Pages\Concerns\HydePage;

/**
 * Converts a Hyde page object into a static HTML page.
 */
class StaticPageBuilder
{
    use InteractsWithDirectories;

    /**
     * Invoke the static page builder for the given page.
     */
    public static function handle(HydePage $page): string
    {
        $path = Hyde::sitePath($page->getOutputPath());

        static::needsParentDirectory($path);

        Hyde::shareViewData($page);

        Filesystem::putContents($path, $page->compile());

        return $path;
    }
}
