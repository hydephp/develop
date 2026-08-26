<?php

declare(strict_types=1);

namespace Hyde\Testing;

use Hyde\Hyde;
use Hyde\Framework\Features\Navigation\NavigationItem;
use Hyde\Framework\Features\Navigation\NavigationMenu;

/**
 * Helpers for testing the site localization feature.
 *
 * The fixtures are created per test rather than taken from the project's own content, so that
 * the tests describe the site they assert on, and neither depend on, nor disturb, whatever
 * the surrounding project happens to contain.
 *
 * For the same reason, localize into a language the surrounding project does not use. Cleaning
 * up a temporary file removes the directory it was created in, so writing companion sources
 * into a language the project also uses would take the project's own sources with them.
 */
trait LocalizesSites
{
    /**
     * Configure the site languages and rediscover, so that the pages and routes
     * of the site under test are the ones the configured languages imply.
     *
     * @param  array<string>|array<string, string>  $languages
     */
    protected function withLanguages(array $languages = ['en', 'de']): void
    {
        config(['localization.languages' => $languages]);

        $this->rediscover();
    }

    /** Rebuild the file, page, and route collections from the current state of the project. */
    protected function rediscover(): void
    {
        Hyde::boot();
    }

    /** Create a canonical source file, which the test case removes when it is done. */
    protected function source(string $path, string $title, string $body): void
    {
        $this->file($path, "---\ntitle: $title\n---\n\n$body\n");
    }

    /** Create a companion source file supplying a page's content in the given language. */
    protected function localizedSource(string $language, string $path, string $title, string $body): void
    {
        $this->source("_locales/$language/$path", $title, $body);
    }

    /** @return array<string> The route keys of the pages linked by the menu items. */
    protected function menuRouteKeys(NavigationMenu $menu): array
    {
        return $menu->getItems()->map(function ($item): ?string {
            return $item instanceof NavigationItem ? $item->getPage()?->getRouteKey() : null;
        })->filter()->sort()->values()->all();
    }
}
