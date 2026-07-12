<?php

declare(strict_types=1);

namespace Hyde\Facades;

use Closure;
use Hyde\Facades\Config;
use Illuminate\Support\Facades\App;

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
