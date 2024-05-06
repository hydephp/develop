<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Testing\UnitTestCase;
use Hyde\Framework\Features\Navigation\FilenamePrefixNavigationHelper;

/**
 * @covers \Hyde\Framework\Features\Navigation\FilenamePrefixNavigationHelper
 *
 * @see \Hyde\Framework\Testing\Feature\FilenamePrefixNavigationPriorityTest
 */
class FilenamePrefixNavigationPriorityUnitTest extends UnitTestCase
{
    protected static bool $needsConfig = true;

    public function testEnabledReturnsTrueWhenEnabled()
    {
        $this->assertTrue(FilenamePrefixNavigationHelper::enabled());
    }

    public function testEnabledReturnsFalseWhenDisabled()
    {
        self::mockConfig(['hyde.numerical_page_ordering' => false]);

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

    public function testIdentifiersWithNumericalPrefixesAreDetectedWhenUsingSnakeCaseDividers()
    {
        $this->assertTrue(FilenamePrefixNavigationHelper::isIdentifierNumbered('01_home.md'));
        $this->assertTrue(FilenamePrefixNavigationHelper::isIdentifierNumbered('02_about.md'));
        $this->assertTrue(FilenamePrefixNavigationHelper::isIdentifierNumbered('03_contact.md'));
    }

    public function testSplitNumberAndIdentifier()
    {
        $this->assertSame([1, 'home.md'], FilenamePrefixNavigationHelper::splitNumberAndIdentifier('01-home.md'));
        $this->assertSame([2, 'about.md'], FilenamePrefixNavigationHelper::splitNumberAndIdentifier('02-about.md'));
        $this->assertSame([3, 'contact.md'], FilenamePrefixNavigationHelper::splitNumberAndIdentifier('03-contact.md'));
    }

    public function testSplitNumberAndIdentifierForSnakeCaseDividers()
    {
        $this->assertSame([1, 'home.md'], FilenamePrefixNavigationHelper::splitNumberAndIdentifier('01_home.md'));
        $this->assertSame([2, 'about.md'], FilenamePrefixNavigationHelper::splitNumberAndIdentifier('02_about.md'));
        $this->assertSame([3, 'contact.md'], FilenamePrefixNavigationHelper::splitNumberAndIdentifier('03_contact.md'));
    }

    public function testSplitNumberAndIdentifierWithMultipleDigits()
    {
        $this->assertSame([123, 'home.md'], FilenamePrefixNavigationHelper::splitNumberAndIdentifier('123-home.md'));
        $this->assertSame([456, 'about.md'], FilenamePrefixNavigationHelper::splitNumberAndIdentifier('456-about.md'));
        $this->assertSame([789, 'contact.md'], FilenamePrefixNavigationHelper::splitNumberAndIdentifier('789-contact.md'));
    }

    public function testSplitNumberAndIdentifierWithMultipleDigitsAndSnakeCaseDividers()
    {
        $this->assertSame([123, 'home.md'], FilenamePrefixNavigationHelper::splitNumberAndIdentifier('123_home.md'));
        $this->assertSame([456, 'about.md'], FilenamePrefixNavigationHelper::splitNumberAndIdentifier('456_about.md'));
        $this->assertSame([789, 'contact.md'], FilenamePrefixNavigationHelper::splitNumberAndIdentifier('789_contact.md'));
    }

    public function testSplitNumberAndIdentifierThrowsExceptionWhenIdentifierIsNotNumbered()
    {
        $this->markTestSkipped('Since this is an internal class at the moment, we do not need to test this. If we want this in the public API it should be a badmethodcall exception.');

        $this->expectException(\AssertionError::class);
        $this->expectExceptionMessage('Identifier "home.md" is not numbered.');

        FilenamePrefixNavigationHelper::splitNumberAndIdentifier('home.md');
    }

    public function testIdentifiersForNestedPagesWithNumericalPrefixesAreDetected()
    {
        $this->assertTrue(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo/01-home.md'));
        $this->assertTrue(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo/02-about.md'));
        $this->assertTrue(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo/03-contact.md'));
    }

    public function testIdentifiersForNestedPagesWithNumericalPrefixesAreDetectedUsingSnakeCaseDividers()
    {
        $this->assertTrue(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo/01_home.md'));
        $this->assertTrue(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo/02_about.md'));
        $this->assertTrue(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo/03_contact.md'));
    }

    public function testIdentifiersForNestedPagesWithoutNumericalPrefixesAreNotDetected()
    {
        $this->assertFalse(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo/home.md'));
        $this->assertFalse(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo/about.md'));
        $this->assertFalse(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo/contact.md'));
    }

    public function testSplitNumberAndIdentifierForNestedPages()
    {
        $this->assertSame([1, 'foo/home.md'], FilenamePrefixNavigationHelper::splitNumberAndIdentifier('foo/01-home.md'));
        $this->assertSame([2, 'foo/about.md'], FilenamePrefixNavigationHelper::splitNumberAndIdentifier('foo/02-about.md'));
        $this->assertSame([3, 'foo/contact.md'], FilenamePrefixNavigationHelper::splitNumberAndIdentifier('foo/03-contact.md'));
    }

    public function testSplitNumberAndIdentifierForNestedPagesWithSnakeCaseDividers()
    {
        $this->assertSame([1, 'foo/home.md'], FilenamePrefixNavigationHelper::splitNumberAndIdentifier('foo/01_home.md'));
        $this->assertSame([2, 'foo/about.md'], FilenamePrefixNavigationHelper::splitNumberAndIdentifier('foo/02_about.md'));
        $this->assertSame([3, 'foo/contact.md'], FilenamePrefixNavigationHelper::splitNumberAndIdentifier('foo/03_contact.md'));
    }

    public function testIdentifiersForDeeplyNestedPagesWithNumericalPrefixesAreDetected()
    {
        $this->assertTrue(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo/bar/01-home.md'));
        $this->assertTrue(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo/bar/02-about.md'));
        $this->assertTrue(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo/bar/03-contact.md'));
    }

    public function testIdentifiersForDeeplyNestedPagesWithNumericalPrefixesAreDetectedUsingSnakeCaseDividers()
    {
        $this->assertTrue(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo/bar/01_home.md'));
        $this->assertTrue(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo/bar/02_about.md'));
        $this->assertTrue(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo/bar/03_contact.md'));
    }

    public function testIdentifiersForDeeplyNestedPagesWithoutNumericalPrefixesAreNotDetected()
    {
        $this->assertFalse(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo/bar/home.md'));
        $this->assertFalse(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo/bar/about.md'));
        $this->assertFalse(FilenamePrefixNavigationHelper::isIdentifierNumbered('foo/bar/contact.md'));
    }

    public function testSplitNumberAndIdentifierForDeeplyNestedPages()
    {
        $this->assertSame([1, 'foo/bar/home.md'], FilenamePrefixNavigationHelper::splitNumberAndIdentifier('foo/bar/01-home.md'));
        $this->assertSame([2, 'foo/bar/about.md'], FilenamePrefixNavigationHelper::splitNumberAndIdentifier('foo/bar/02-about.md'));
        $this->assertSame([3, 'foo/bar/contact.md'], FilenamePrefixNavigationHelper::splitNumberAndIdentifier('foo/bar/03-contact.md'));
    }

    public function testSplitNumberAndIdentifierForDeeplyNestedPagesWithSnakeCaseDividers()
    {
        $this->assertSame([1, 'foo/bar/home.md'], FilenamePrefixNavigationHelper::splitNumberAndIdentifier('foo/bar/01_home.md'));
        $this->assertSame([2, 'foo/bar/about.md'], FilenamePrefixNavigationHelper::splitNumberAndIdentifier('foo/bar/02_about.md'));
        $this->assertSame([3, 'foo/bar/contact.md'], FilenamePrefixNavigationHelper::splitNumberAndIdentifier('foo/bar/03_contact.md'));
    }
}
