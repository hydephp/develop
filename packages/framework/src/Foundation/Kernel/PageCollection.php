<?php

declare(strict_types=1);

namespace Hyde\Foundation\Kernel;

use Hyde\Hyde;
use Hyde\Facades\Localization;
use Hyde\Foundation\Concerns\BaseFoundationCollection;
use Hyde\Framework\Exceptions\FileNotFoundException;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Support\Filesystem\SourceFile;
use Hyde\Support\Models\Redirect;

use function str_repeat;
use function substr_count;

/**
 * The PageCollection contains all the instantiated pages.
 *
 * @template T of \Hyde\Pages\Concerns\HydePage
 *
 * @extends \Hyde\Foundation\Concerns\BaseFoundationCollection<string, T>
 *
 * @property array<string, HydePage> $items The pages in the collection.
 *
 * @method HydePage|null get(string $key, HydePage $default = null)
 *
 * This class is stored as a singleton in the HydeKernel.
 * You would commonly access it via the facade or Hyde helper:
 *
 * @see \Hyde\Foundation\Facades\PageCollection
 * @see \Hyde\Hyde::pages()
 */
final class PageCollection extends BaseFoundationCollection
{
    public function addPage(HydePage $page): void
    {
        $this->put(static::makeKey($page), $page);
    }

    protected function runDiscovery(): void
    {
        $this->kernel->files()->each(function (SourceFile $file): void {
            $page = $this->parsePage($file->pageClass, $file->getPath());

            if (Localization::enabled()) {
                foreach (Localization::languages() as $language) {
                    $this->addPage($page->withLanguage($language));
                }

                $this->addDefaultLanguageRedirect($page);
            } else {
                $this->addPage($page);
            }
        });
    }

    /**
     * Add a redirect from the unprefixed route key to the default language, so that
     * for example `/foo` sends the visitor to `/en/foo` when English is the default.
     */
    protected function addDefaultLanguageRedirect(HydePage $page): void
    {
        $routeKey = $page->getRouteKey();

        $this->addPage(new Redirect($routeKey, static::makeRedirectDestination($routeKey), matter: [
            'navigation' => ['hidden' => true],
        ]));
    }

    protected static function makeRedirectDestination(string $routeKey): string
    {
        // The destination is relative to the redirect page, which sits at the unprefixed
        // route key, so we need to walk back up to the site webroot before descending
        // into the language directory. For example, `posts/hello` redirects to
        // `../en/posts/hello.html`, which resolves to `/en/posts/hello.html`.

        $depth = substr_count($routeKey, '/');

        return str_repeat('../', $depth).Hyde::formatLink(Localization::defaultLanguage()."/$routeKey.html");
    }

    protected function runExtensionHandlers(): void
    {
        /** @var class-string<\Hyde\Foundation\Concerns\HydeExtension> $extension */
        foreach ($this->kernel->getExtensions() as $extension) {
            $extension->discoverPages($this);
        }
    }

    /** @param  class-string<\Hyde\Pages\Concerns\HydePage>  $pageClass */
    protected static function parsePage(string $pageClass, string $path): HydePage
    {
        return $pageClass::parse($pageClass::pathToIdentifier($path));
    }

    public function getPage(string $sourcePath): HydePage
    {
        // When the site is localized, each source file has one page per language,
        // so we resolve the page for the default language when given a source path.

        return $this->get($sourcePath)
            ?? $this->get(static::makeLocalizedKey(Localization::defaultLanguage(), $sourcePath))
            ?? throw new FileNotFoundException($sourcePath);
    }

    protected static function makeKey(HydePage $page): string
    {
        return $page->getLanguage() === null
            ? $page->getSourcePath()
            : static::makeLocalizedKey($page->getLanguage(), $page->getSourcePath());
    }

    protected static function makeLocalizedKey(string $language, string $sourcePath): string
    {
        return "$language::$sourcePath";
    }

    /** @param  class-string<\Hyde\Pages\Concerns\HydePage>|null  $pageClass */
    public function getPages(?string $pageClass = null): PageCollection
    {
        return $pageClass ? $this->filter(function (HydePage $page) use ($pageClass): bool {
            return $page instanceof $pageClass;
        }) : $this;
    }
}
