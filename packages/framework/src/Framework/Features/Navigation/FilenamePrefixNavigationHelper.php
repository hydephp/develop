<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Facades\Config;
use Illuminate\Support\Str;

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
        if (self::isIdentifierNested($identifier)) {
            $identifier = self::getCoreIdentifierPart($identifier);
        }

        return preg_match('/^\d+-/', $identifier) === 1;
    }

    /**
     * Splits a numbered identifier into its numerical prefix and the rest of the identifier.
     *
     * @return array{integer, string}
     */
    public static function splitNumberAndIdentifier(string $identifier): array
    {
        if (self::isIdentifierNested($identifier)) {
            $parentPath = self::getNestedIdentifierPrefix($identifier);
            $identifier = self::getCoreIdentifierPart($identifier);
        }

        assert(self::isIdentifierNumbered($identifier));

        $parts = explode('-', $identifier, 2);

        $parts[0] = (int) $parts[0];

        if (isset($parentPath)) {
            $parts[1] = $parentPath . '/' . $parts[1];
        }

        return $parts;
    }

    protected static function isIdentifierNested(string $identifier): bool
    {
        return str_contains($identifier, '/');
    }

    protected static function getNestedIdentifierPrefix(string $identifier): string
    {
        assert(self::isIdentifierNested($identifier));

        return Str::beforeLast($identifier, '/');
    }

    protected static function getCoreIdentifierPart(string $identifier): string
    {
        assert(self::isIdentifierNested($identifier));

        return Str::afterLast($identifier, '/');
    }
}
