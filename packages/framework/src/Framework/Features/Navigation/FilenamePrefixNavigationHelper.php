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
    /**
     * Determines if the feature is enabled.
     */
    public static function enabled(): bool
    {
        return true;
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

    public static function getTest(): UnitTestCase
    {
        return new class('FilenamePrefixNavigationHelperTest') extends UnitTestCase
        {
            public function testEnabledReturnsTrueWhenEnabled()
            {
                $this->assertTrue(FilenamePrefixNavigationHelper::enabled());
            }

            public function testEnabledReturnsFalseWhenDisabled()
            {
                $this->markTestSkipped('TODO: Support for disabling the feature.');
            }

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

            public function testSplitNumberAndIdentifier()
            {
                $this->assertSame([1, 'home.md'], FilenamePrefixNavigationHelper::splitNumberAndIdentifier('01-home.md'));
                $this->assertSame([2, 'about.md'], FilenamePrefixNavigationHelper::splitNumberAndIdentifier('02-about.md'));
                $this->assertSame([3, 'contact.md'], FilenamePrefixNavigationHelper::splitNumberAndIdentifier('03-contact.md'));
            }

            public function testSplitNumberAndIdentifierWithMultipleDigits()
            {
                $this->assertSame([123, 'home.md'], FilenamePrefixNavigationHelper::splitNumberAndIdentifier('123-home.md'));
                $this->assertSame([456, 'about.md'], FilenamePrefixNavigationHelper::splitNumberAndIdentifier('456-about.md'));
                $this->assertSame([789, 'contact.md'], FilenamePrefixNavigationHelper::splitNumberAndIdentifier('789-contact.md'));
            }

            public function testSplitNumberAndIdentifierThrowsExceptionWhenIdentifierIsNotNumbered()
            {
                $this->expectException(\AssertionError::class);
                // $this->expectExceptionMessage('Identifier "home.md" is not numbered.');

                FilenamePrefixNavigationHelper::splitNumberAndIdentifier('home.md');
            }
        };
    }
}
