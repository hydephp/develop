<?php

declare(strict_types=1);

namespace Hyde\Facades;

use Closure;
use Illuminate\Support\Facades\App;

use function Hyde\unslash;
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
     * Run the callback using the given language as the app locale, so that translation
     * strings are resolved for it, then restore the previously active locale.
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
