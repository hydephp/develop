<?php

declare(strict_types=1);

namespace Hyde\Facades;

use Hyde\Facades\Config;

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
}
