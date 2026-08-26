<?php

declare(strict_types=1);

namespace Hyde\Facades;

use Closure;
use Hyde\Pages\Concerns\HydePage;
use Illuminate\Support\Facades\App;
use Hyde\Framework\Exceptions\InvalidConfigurationException;

use function app;
use function str_starts_with;
use function strlen;
use function substr;
use function array_keys;
use function is_string;
use function count;
use function strtolower;
use function str_replace;
use function preg_match;
use function in_array;

use function Hyde\unslash;

/**
 * General facade for interacting with the site localization settings.
 *
 * When one or more languages are configured, the site is compiled once for each
 * language, with each version being placed in a subdirectory named after it.
 */
class Localization
{
    /**
     * A language identifier is a web/BCP-47-style language tag: one or more alphanumeric
     * subtags separated by single hyphens, such as `en`, `en-GB`, `zh-Hant`, or `es-419`.
     *
     * This is deliberately not a full IANA language subtag registry validator. It only
     * rejects identifiers that would be unsafe as a path segment or ambiguous in a URL.
     */
    private const LANGUAGE_TAG_PATTERN = '/^[a-zA-Z0-9]+(-[a-zA-Z0-9]+)*$/';

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
        return array_keys(static::languageNames());
    }

    /**
     * Get the display name of the given language, which is the language code itself
     * unless the site configures a name for it.
     */
    public static function label(string $language): string
    {
        return static::languageNames()[$language] ?? $language;
    }

    /**
     * Get the configured languages, as a map of language code to display name.
     *
     * Languages can be configured as a list of codes, or as a map of code to display name,
     * so that a language switcher can offer 'Svenska' rather than 'sv'. A language
     * configured without a name uses its code as its name.
     *
     * Every identifier is validated as a web language tag, and no two configured
     * identifiers may be the same when compared case-insensitively.
     *
     * @return array<string, string>
     */
    protected static function languageNames(): array
    {
        $languages = [];
        $seen = [];

        foreach (Config::getArray('localization.languages', []) as $key => $name) {
            $language = is_string($key) ? $key : (string) $name;

            static::validateLanguageIdentifier($language);

            $normalized = strtolower($language);

            if (isset($seen[$normalized])) {
                throw new InvalidConfigurationException(
                    "The language [$language] is configured more than once. Language identifiers must be unique, even when compared case-insensitively.",
                    'localization',
                    'languages'
                );
            }

            $seen[$normalized] = true;
            $languages[$language] = (string) $name;
        }

        return $languages;
    }

    /**
     * Validate that the given string is a well-formed web language tag, safe to use as a
     * single path segment, such as `en`, `sv`, or `en-GB`.
     */
    protected static function validateLanguageIdentifier(string $language): void
    {
        if (preg_match(self::LANGUAGE_TAG_PATTERN, $language) !== 1) {
            throw new InvalidConfigurationException(
                "The configured language identifier [$language] is not a supported language identifier. Language identifiers must be BCP-47-style tags composed of hyphen-separated alphanumeric subtags, such as 'en' or 'en-GB'.",
                'localization',
                'languages'
            );
        }
    }

    /**
     * Get the default language of the site, which is the first configured language,
     * or null when the site is not localized.
     */
    public static function defaultLanguage(): ?string
    {
        return static::languages()[0] ?? null;
    }

    /**
     * Determine if the given language is one of the site's configured languages.
     */
    public static function isConfiguredLanguage(string $language): bool
    {
        return in_array($language, static::languages(), true);
    }

    /**
     * Get the language currently in effect, which is the configured language corresponding
     * to Laravel's currently active locale, since that is what a render establishes for
     * the duration of a page being compiled.
     *
     * Returns null when the site is not localized, so that passing this to a language
     * filter matches every route, rather than none of them.
     */
    public static function currentLanguage(): ?string
    {
        if (! static::enabled()) {
            return null;
        }

        $language = static::fromLaravelLocale(App::currentLocale());

        foreach (static::languages() as $configured) {
            if (strtolower($configured) === strtolower($language)) {
                return $configured;
            }
        }

        return static::defaultLanguage();
    }

    /**
     * Prefix a route key or output path with the given language directory.
     *
     * Passing a null language returns the path as is. The operation is deliberately dumb:
     * it knows nothing about file extensions, so paths like `feed.xml`, `search.json`,
     * and `about.html` are all prefixed the same way, into their language directory.
     *
     * @internal Used by route keys and output paths to apply localization.
     */
    public static function prefixPath(string $path, ?string $language): string
    {
        return $language === null ? $path : unslash("$language/".unslash($path));
    }

    /**
     * Determine whether a route key or redirect destination already names a configured
     * language as its own first path segment, such as `en/about`, or `en` on its own.
     *
     * Configured language identifiers are reserved first route-key segments: a key that
     * begins with one refers to that language explicitly, rather than one to be resolved
     * within whichever language is currently being rendered.
     *
     * @internal Used by route resolution and redirect destination handling.
     */
    public static function isLanguagePrefixed(string $path): bool
    {
        foreach (static::languages() as $language) {
            if ($path === $language || str_starts_with($path, "$language/")) {
                return true;
            }
        }

        return false;
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
     *
     * @internal Used by companionSourcePath(); publish `config/localization.php` to change it.
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
     *
     * @internal Used by page classes that support companion sources, such as BaseMarkdownPage.
     */
    public static function companionSourcePath(string $sourcePath, string $language): ?string
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
     *
     * @internal Used to translate navigation labels; use the `__()` helper directly elsewhere.
     */
    public static function translate(string $string): string
    {
        return app()->bound('translator') ? __($string) : $string;
    }

    /**
     * Get the output path of every language variant of the given page, keyed by language.
     *
     * This is what links the languages of a page together, for hreflang metadata and for
     * language switchers. Returns an empty array when the site is not localized, or when
     * the page is not compiled once per language to begin with.
     *
     * The paths are derived from the page rather than looked up in the route collection,
     * as page metadata is generated while that collection is still being built.
     *
     * @return array<string, string>
     */
    public static function alternates(HydePage $page): array
    {
        if (! static::enabled() || ! $page->isLocalizable()) {
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
     *
     * @internal Used by alternates() and documentation versioning to reverse prefixPath().
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
     * @internal Render-context mechanism; page language is validated through withLanguage()
     *           before it ever reaches here, so this does not itself check the language
     *           is one of the site's configured languages.
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

        App::setLocale(static::toLaravelLocale($language));

        try {
            return $callback();
        } finally {
            App::setLocale($locale);
        }
    }

    /**
     * Convert a configured web language tag to the locale form Laravel's translator resolves
     * translation files with, following the `language_TERRITORY` convention documented for
     * Laravel, for example `en-GB` to `en_GB`.
     *
     * @internal Used to bridge Hyde's BCP-47-style language tags with Laravel's own locale form.
     */
    public static function toLaravelLocale(string $language): string
    {
        return str_replace('-', '_', $language);
    }

    /**
     * Convert a Laravel locale back to its web language tag, the inverse of {@see toLaravelLocale()}.
     *
     * @internal Used to bridge Hyde's BCP-47-style language tags with Laravel's own locale form.
     */
    public static function fromLaravelLocale(string $locale): string
    {
        return str_replace('_', '-', $locale);
    }
}
