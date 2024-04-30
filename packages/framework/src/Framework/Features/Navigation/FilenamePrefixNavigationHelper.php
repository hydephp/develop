<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

/**
 * @internal Helper class for the new Filename Prefix Navigation feature.
 *
 * @experimental The code herein may be moved to more appropriate locations in the future.
 */
class FilenamePrefixNavigationHelper
{
    public static function isIdentifierNumbered(string $identifier): bool
    {
        return preg_match('/^\d+-/', $identifier) === 1;
    }
}
