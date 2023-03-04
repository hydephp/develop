<?php

declare(strict_types=1);

namespace Hyde\Framework\Services;

/**
 * General Discovery Helpers for HydePHP Auto-Discovery.
 *
 * Offloads FoundationCollection logic and provides helpers for common code.
 *
 * @see \Hyde\Framework\Testing\Feature\DiscoveryServiceTest
 */
class DiscoveryService
{
    public static function pathToIdentifier(string $pageClass, string $filepath): string
    {
        return $pageClass::pathToIdentifier($filepath);
    }
}
