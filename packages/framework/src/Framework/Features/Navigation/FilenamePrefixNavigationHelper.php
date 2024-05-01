<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Facades\Config;

use function assert;
use function explode;
use function preg_match;

/**
 * @internal Helper class for the new Filename Prefix Navigation feature.
 *
 * @experimental The code herein may be moved to more appropriate locations in the future.
 */
class FilenamePrefixNavigationHelper
{
    /**
     * Determines if the feature is enabled.
     */
    public static function enabled(): bool
    {
        return Config::getBool('hyde.filename_page_ordering', true);
    }

    /**
     * Determines if a given identifier has a numerical prefix.
     */
    public static function isIdentifierNumbered(string $identifier): bool
    {
        return preg_match('/^\d+-/', $identifier) === 1;
    }

    /**
     * Splits a numbered identifier into its numerical prefix and the rest of the identifier.
     *
     * @return array{integer, string}
     */
    public static function splitNumberAndIdentifier(string $identifier): array
    {
        assert(self::isIdentifierNumbered($identifier));

        $parts = explode('-', $identifier, 2);

        $parts[0] = (int) $parts[0];

        return $parts;
    }

    protected static function isIdentifierNested(string $identifier): bool
    {
        return str_contains($identifier, '/');
    }

    protected static function getCoreIdentifierPart(string $identifier): string
    {
        assert(self::isIdentifierNested($identifier));

        return explode('/', $identifier)[1];
    }
}
