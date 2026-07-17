<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Hyde;
use Hyde\Facades\Filesystem;
use Hyde\Framework\Concerns\InteractsWithDirectories;
use Hyde\Facades\Localization;
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

        Filesystem::putContents($path, static::compilePage($page));

        return $path;
    }

    /**
     * Compile the page, using the page's language as the app locale when the site is localized,
     * so that translation strings are resolved for the language the page is being built for.
     */
    protected static function compilePage(HydePage $page): string
    {
        return Localization::usingLanguage($page->getLanguage(), fn (): string => $page->compile());
    }
}
