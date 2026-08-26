<?php

declare(strict_types=1);

namespace Hyde\Facades;

use Closure;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Support\Facades\Render;
use Illuminate\Support\Facades\App;

use function Hyde\unslash;
use function app;
use function str_starts_with;
use function strlen;
use function substr;
use function count;

/**
 * General facade for interacting with the site localization settings.
 *
 * When one or more languages are configured, the site is compiled once for each
 * language, with each version being placed in a subdirectory named after it.
 */
class Localization
{
    /**
     * Determine if the site is localized, meaning it has at least one configured language.
     */
    public static function enabled(): bool
    {
        return count(static::languages()) > 0;
    }

    /**
     * Get the languages the site is compiled for, in the order they are configured.
     *
     * @return array<string>
     */
    public static function languages(): array
    {
        return array_values(Config::getArray('localization.languages', []));
    }

    /**
     * Get the default language of the site, which is the first configured language.
     */
    public static function defaultLanguage(): string
    {
        return static::languages()[0] ?? Config::getString('app.locale', 'en');
    }

    /**
     * Get the language currently in effect, which is the language of the page being
     * rendered, falling back to the default language when nothing is being rendered.
     *
     * Returns null when the site is not localized, so that passing this to a language
     * filter matches every route, rather than none of them.
     */
    public static function currentLanguage(): ?string
    {
        if (! static::enabled()) {
            return null;
        }

        return Render::getPage()?->getLanguage() ?? static::defaultLanguage();
    }

    /**
     * Prefix a route key or output path with the given language directory.
     *
     * Passing a null language returns the path as is. The operation is deliberately dumb:
     * it knows nothing about file extensions, so paths like `feed.xml`, `search.json`,
     * and `about.html` are all prefixed the same way, into their language directory.
     */
    public static function prefixPath(string $path, ?string $language): string
    {
        return $language === null ? $path : unslash("$language/".unslash($path));
    }

    /**
     * Get the language to declare on the html element of the page being rendered.
     *
     * The language currently in effect when the site is localized, and the configured site
     * language otherwise, which is where a site with a single language declares it.
     */
    public static function htmlLanguage(): string
    {
        return static::currentLanguage() ?? Config::getString('hyde.language', 'en');
    }

    /**
     * Get the directory holding the localized companion sources of the site.
     */
    public static function sourceDirectory(): string
    {
        return Config::getString('localization.source_directory', '_locales');
    }

    /**
     * Get the path to the companion source file supplying the given source file's content
     * in the given language, or null when that language has no companion file for it.
     *
     * A page is authored once and compiled into every language, so a language without its
     * own source for a page falls back to the canonical one, rendered in its language.
     * That lets a site be translated a page at a time, without any page going
     * missing from a language while its translation is still outstanding.
     */
    public static function sourcePath(string $sourcePath, string $language): ?string
    {
        $path = static::sourceDirectory()."/$language/".unslash($sourcePath);

        return Filesystem::exists($path) ? $path : null;
    }

    /**
     * Translate a string, when there is a translator to translate it with.
     *
     * Navigation labels are translated through this, so that a localized site can translate
     * its menus. Navigation is also constructed in contexts that have no booted
     * application, where the string can only be returned as it was given.
     */
    public static function translate(string $string): string
    {
        return app()->bound('translator') ? __($string) : $string;
    }

    /**
     * Get the output path of every language variant of the given page, keyed by language.
     *
     * This is what links the languages of a page together, for hreflang metadata and for
     * language switchers. Returns an empty array when the site is not localized.
     *
     * The paths are derived from the page rather than looked up in the route collection,
     * as page metadata is generated while that collection is still being built.
     *
     * @return array<string, string>
     */
    public static function alternates(HydePage $page): array
    {
        if (! static::enabled()) {
            return [];
        }

        $outputPath = static::stripPrefix($page->getOutputPath(), $page->getLanguage());

        $paths = [];

        foreach (static::languages() as $language) {
            $paths[$language] = static::prefixPath($outputPath, $language);
        }

        return $paths;
    }

    /**
     * Strip the language directory from a route key or output path.
     *
     * This is the inverse of {@see prefixPath()}, for the cases where a path has to be
     * matched against an unlocalized one, such as resolving an incoming request URL.
     */
    public static function stripPrefix(string $path, ?string $language): string
    {
        if ($language === null || ! str_starts_with($path, "$language/")) {
            return $path;
        }

        return substr($path, strlen($language) + 1);
    }

    /**
     * Establish a language context, running the callback with the given language as the
     * app locale, so that translation strings are resolved for it, then restore the
     * previously active locale.
     *
     * Everything belonging to one language should run within a single context, rather than
     * the context wrapping one call within it. {@see \Hyde\Hyde::renderPage()}
     *
     * Passing a null language runs the callback as is, using the default locale.
     *
     * @template T
     *
     * @param  \Closure(): T  $callback
     * @return T
     */
    public static function usingLanguage(?string $language, Closure $callback): mixed
    {
        if ($language === null) {
            return $callback();
        }

        $locale = App::getLocale();

        App::setLocale($language);

        try {
            return $callback();
        } finally {
            App::setLocale($locale);
        }
    }
}
