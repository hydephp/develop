<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Testing\UnitTestCase;
use Hyde\Framework\Features\Navigation\FilenamePrefixNavigationHelper;

class FilenamePrefixNavigationPriorityUnitTest extends UnitTestCase
{
    protected static bool $needsConfig = true;

    public function testEnabledReturnsTrueWhenEnabled()
    {
        $this->assertTrue(FilenamePrefixNavigationHelper::enabled());
    }

    public function testEnabledReturnsFalseWhenDisabled()
    {
        self::mockConfig(['hyde.filename_page_ordering' => false]);

        $this->assertFalse(FilenamePrefixNavigationHelper::enabled());
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
        $this->markTestSkipped('Since this is an internal class at the moment, we don not need to test this. If we want this in the public API it should be a badmethodcall exception.');

        $this->expectException(\AssertionError::class);
        $this->expectExceptionMessage('Identifier "home.md" is not numbered.');

        FilenamePrefixNavigationHelper::splitNumberAndIdentifier('home.md');
    }
}
