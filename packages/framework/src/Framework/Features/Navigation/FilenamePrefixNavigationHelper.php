<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use Hyde\Testing\UnitTestCase;

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

    public static function getTest(): UnitTestCase
    {
        return new class('FilenamePrefixNavigationHelperTest') extends UnitTestCase
        {
            public function testIdentifiersWithNumericalPrefixesAreDetected()
            {
                $this->assertTrue(FilenamePrefixNavigationHelper::isIdentifierNumbered('01-home.md'));
                $this->assertTrue(FilenamePrefixNavigationHelper::isIdentifierNumbered('02-about.md'));
                $this->assertTrue(FilenamePrefixNavigationHelper::isIdentifierNumbered('03-contact.md'));
            }

            public function testIdentifiersWithoutNumericalPrefixesAreNotDetected()
            {
                $this->assertFalse(FilenamePrefixNavigationHelper::isIdentifierNumbered('home.md'));
                $this->assertFalse(FilenamePrefixNavigationHelper::isIdentifierNumbered('about.md'));
                $this->assertFalse(FilenamePrefixNavigationHelper::isIdentifierNumbered('contact.md'));
            }
        };
    }
}
