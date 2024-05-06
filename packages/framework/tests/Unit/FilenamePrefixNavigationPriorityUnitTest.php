<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Testing\UnitTestCase;
use Hyde\Foundation\HydeCoreExtension;
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
        $this->assertTrue(FilenamePrefixNavigationHelper::hasNumericalPrefix('01-home.md'));
        $this->assertTrue(FilenamePrefixNavigationHelper::hasNumericalPrefix('02-about.md'));
        $this->assertTrue(FilenamePrefixNavigationHelper::hasNumericalPrefix('03-contact.md'));
    }

    public function testIdentifiersWithoutNumericalPrefixesAreNotDetected()
    {
        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('home.md'));
        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('about.md'));
        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('contact.md'));
    }

    public function testIdentifiersWithNumericalPrefixesAreDetectedWhenUsingSnakeCaseDividers()
    {
        $this->assertTrue(FilenamePrefixNavigationHelper::hasNumericalPrefix('01_home.md'));
        $this->assertTrue(FilenamePrefixNavigationHelper::hasNumericalPrefix('02_about.md'));
        $this->assertTrue(FilenamePrefixNavigationHelper::hasNumericalPrefix('03_contact.md'));
    }

    public function testSplitNumericPrefix()
    {
        $this->assertSame([1, 'home.md'], FilenamePrefixNavigationHelper::splitNumericPrefix('01-home.md'));
        $this->assertSame([2, 'about.md'], FilenamePrefixNavigationHelper::splitNumericPrefix('02-about.md'));
        $this->assertSame([3, 'contact.md'], FilenamePrefixNavigationHelper::splitNumericPrefix('03-contact.md'));
    }

    public function testSplitNumericPrefixForSnakeCaseDividers()
    {
        $this->assertSame([1, 'home.md'], FilenamePrefixNavigationHelper::splitNumericPrefix('01_home.md'));
        $this->assertSame([2, 'about.md'], FilenamePrefixNavigationHelper::splitNumericPrefix('02_about.md'));
        $this->assertSame([3, 'contact.md'], FilenamePrefixNavigationHelper::splitNumericPrefix('03_contact.md'));
    }

    public function testSplitNumericPrefixWithMultipleDigits()
    {
        $this->assertSame([123, 'home.md'], FilenamePrefixNavigationHelper::splitNumericPrefix('123-home.md'));
        $this->assertSame([456, 'about.md'], FilenamePrefixNavigationHelper::splitNumericPrefix('456-about.md'));
        $this->assertSame([789, 'contact.md'], FilenamePrefixNavigationHelper::splitNumericPrefix('789-contact.md'));
    }

    public function testSplitNumericPrefixWithMultipleDigitsAndSnakeCaseDividers()
    {
        $this->assertSame([123, 'home.md'], FilenamePrefixNavigationHelper::splitNumericPrefix('123_home.md'));
        $this->assertSame([456, 'about.md'], FilenamePrefixNavigationHelper::splitNumericPrefix('456_about.md'));
        $this->assertSame([789, 'contact.md'], FilenamePrefixNavigationHelper::splitNumericPrefix('789_contact.md'));
    }

    public function testSplitNumericPrefixThrowsExceptionWhenIdentifierIsNotNumbered()
    {
        $this->markTestSkipped('Since this is an internal class at the moment, we do not need to test this. If we want this in the public API it should be a badmethodcall exception.');

        $this->expectException(\AssertionError::class);
        $this->expectExceptionMessage('Identifier "home.md" is not numbered.');

        FilenamePrefixNavigationHelper::splitNumericPrefix('home.md');
    }

    public function testIdentifiersForNestedPagesWithNumericalPrefixesAreDetected()
    {
        $this->assertTrue(FilenamePrefixNavigationHelper::hasNumericalPrefix('foo/01-home.md'));
        $this->assertTrue(FilenamePrefixNavigationHelper::hasNumericalPrefix('foo/02-about.md'));
        $this->assertTrue(FilenamePrefixNavigationHelper::hasNumericalPrefix('foo/03-contact.md'));
    }

    public function testIdentifiersForNestedPagesWithNumericalPrefixesAreDetectedUsingSnakeCaseDividers()
    {
        $this->assertTrue(FilenamePrefixNavigationHelper::hasNumericalPrefix('foo/01_home.md'));
        $this->assertTrue(FilenamePrefixNavigationHelper::hasNumericalPrefix('foo/02_about.md'));
        $this->assertTrue(FilenamePrefixNavigationHelper::hasNumericalPrefix('foo/03_contact.md'));
    }

    public function testIdentifiersForNestedPagesWithoutNumericalPrefixesAreNotDetected()
    {
        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('foo/home.md'));
        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('foo/about.md'));
        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('foo/contact.md'));
    }

    public function testSplitNumericPrefixForNestedPages()
    {
        $this->assertSame([1, 'foo/home.md'], FilenamePrefixNavigationHelper::splitNumericPrefix('foo/01-home.md'));
        $this->assertSame([2, 'foo/about.md'], FilenamePrefixNavigationHelper::splitNumericPrefix('foo/02-about.md'));
        $this->assertSame([3, 'foo/contact.md'], FilenamePrefixNavigationHelper::splitNumericPrefix('foo/03-contact.md'));
    }

    public function testSplitNumericPrefixForNestedPagesWithSnakeCaseDividers()
    {
        $this->assertSame([1, 'foo/home.md'], FilenamePrefixNavigationHelper::splitNumericPrefix('foo/01_home.md'));
        $this->assertSame([2, 'foo/about.md'], FilenamePrefixNavigationHelper::splitNumericPrefix('foo/02_about.md'));
        $this->assertSame([3, 'foo/contact.md'], FilenamePrefixNavigationHelper::splitNumericPrefix('foo/03_contact.md'));
    }

    public function testIdentifiersForDeeplyNestedPagesWithNumericalPrefixesAreDetected()
    {
        $this->assertTrue(FilenamePrefixNavigationHelper::hasNumericalPrefix('foo/bar/01-home.md'));
        $this->assertTrue(FilenamePrefixNavigationHelper::hasNumericalPrefix('foo/bar/02-about.md'));
        $this->assertTrue(FilenamePrefixNavigationHelper::hasNumericalPrefix('foo/bar/03-contact.md'));
    }

    public function testIdentifiersForDeeplyNestedPagesWithNumericalPrefixesAreDetectedUsingSnakeCaseDividers()
    {
        $this->assertTrue(FilenamePrefixNavigationHelper::hasNumericalPrefix('foo/bar/01_home.md'));
        $this->assertTrue(FilenamePrefixNavigationHelper::hasNumericalPrefix('foo/bar/02_about.md'));
        $this->assertTrue(FilenamePrefixNavigationHelper::hasNumericalPrefix('foo/bar/03_contact.md'));
    }

    public function testIdentifiersForDeeplyNestedPagesWithoutNumericalPrefixesAreNotDetected()
    {
        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('foo/bar/home.md'));
        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('foo/bar/about.md'));
        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('foo/bar/contact.md'));
    }

    /**
     * @param  class-string<\Hyde\Pages\Concerns\HydePage>  $type
     *
     * @dataProvider pageTypeProvider
     */
    public function testIdentifiersWithNumericalPrefixesAreDetectedForPageType(string $type)
    {
        $this->assertTrue(FilenamePrefixNavigationHelper::hasNumericalPrefix('01-home.'.$type::$fileExtension));
        $this->assertTrue(FilenamePrefixNavigationHelper::hasNumericalPrefix('02-about.'.$type::$fileExtension));
        $this->assertTrue(FilenamePrefixNavigationHelper::hasNumericalPrefix('03-contact.'.$type::$fileExtension));
    }

    /**
     * @param  class-string<\Hyde\Pages\Concerns\HydePage>  $type
     *
     * @dataProvider pageTypeProvider
     */
    public function testIdentifiersWithoutNumericalPrefixesAreNotDetectedForPageType(string $type)
    {
        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('home.'.$type::$fileExtension));
        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('about.'.$type::$fileExtension));
        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('contact.'.$type::$fileExtension));
    }

    /**
     * @param  class-string<\Hyde\Pages\Concerns\HydePage>  $type
     *
     * @dataProvider pageTypeProvider
     */
    public function testIdentifiersWithNumericalPrefixesAreDetectedWhenUsingSnakeCaseDividersForPageType(string $type)
    {
        $this->assertTrue(FilenamePrefixNavigationHelper::hasNumericalPrefix('01_home.'.$type::$fileExtension));
        $this->assertTrue(FilenamePrefixNavigationHelper::hasNumericalPrefix('02_about.'.$type::$fileExtension));
        $this->assertTrue(FilenamePrefixNavigationHelper::hasNumericalPrefix('03_contact.'.$type::$fileExtension));
    }

    /**
     * @param  class-string<\Hyde\Pages\Concerns\HydePage>  $type
     *
     * @dataProvider pageTypeProvider
     */
    public function testSplitNumericPrefixForDeeplyNestedPagesForPageType(string $type)
    {
        $this->assertSame([1, 'foo/bar/home.'.$type::$fileExtension], FilenamePrefixNavigationHelper::splitNumericPrefix('foo/bar/01-home.'.$type::$fileExtension));
        $this->assertSame([2, 'foo/bar/about.'.$type::$fileExtension], FilenamePrefixNavigationHelper::splitNumericPrefix('foo/bar/02-about.'.$type::$fileExtension));
        $this->assertSame([3, 'foo/bar/contact.'.$type::$fileExtension], FilenamePrefixNavigationHelper::splitNumericPrefix('foo/bar/03-contact.'.$type::$fileExtension));
    }

    public function testSplitNumericPrefixForDeeplyNestedPages()
    {
        $this->assertSame([1, 'foo/bar/home.md'], FilenamePrefixNavigationHelper::splitNumericPrefix('foo/bar/01-home.md'));
        $this->assertSame([2, 'foo/bar/about.md'], FilenamePrefixNavigationHelper::splitNumericPrefix('foo/bar/02-about.md'));
        $this->assertSame([3, 'foo/bar/contact.md'], FilenamePrefixNavigationHelper::splitNumericPrefix('foo/bar/03-contact.md'));
    }

    public function testSplitNumericPrefixForDeeplyNestedPagesWithSnakeCaseDividers()
    {
        $this->assertSame([1, 'foo/bar/home.md'], FilenamePrefixNavigationHelper::splitNumericPrefix('foo/bar/01_home.md'));
        $this->assertSame([2, 'foo/bar/about.md'], FilenamePrefixNavigationHelper::splitNumericPrefix('foo/bar/02_about.md'));
        $this->assertSame([3, 'foo/bar/contact.md'], FilenamePrefixNavigationHelper::splitNumericPrefix('foo/bar/03_contact.md'));
    }

    public function testNonNumericalPartsAreNotDetected()
    {
        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('foo-bar.md'));
        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('foo-bar.md'));
        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('foo-bar.md'));

        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('foo-bar/home.md'));
        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('foo-bar/about.md'));
        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('foo-bar/contact.md'));
    }

    public function testNonNumericalPartsAreNotDetectedForSnakeCaseDividers()
    {
        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('foo_bar.md'));
        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('foo_bar.md'));
        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('foo_bar.md'));

        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('foo_bar/home.md'));
        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('foo_bar/about.md'));
        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('foo_bar/contact.md'));
    }

    public function testNumericallyPrefixedIdentifiersWithUnknownDividersAreNotDetected()
    {
        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('1.foo.md'));
        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('01.foo.md'));
        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('001.foo.md'));

        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('1/foo.md'));
        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('01/foo.md'));
        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('001/foo.md'));

        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('1—foo.md'));
        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('01—foo.md'));
        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('001—foo.md'));

        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('1 foo.md'));
        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('01 foo.md'));
        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('001 foo.md'));
    }

    public function testNumericallyPrefixedIdentifiersWithoutDividerAreNotDetected()
    {
        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('1foo.md'));
        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('01foo.md'));
        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('001foo.md'));
    }

    public function testNumericallyStringPrefixedIdentifiersWithoutDividerAreNotDetected()
    {
        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('one-foo.md'));
        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('one_foo.md'));
        $this->assertFalse(FilenamePrefixNavigationHelper::hasNumericalPrefix('one.foo.md'));
    }

    public static function pageTypeProvider(): array
    {
        self::needsKernel();
        self::mockConfig();

        return array_combine(
            array_map(fn ($class) => str($class)->classBasename()->snake(' ')->plural()->toString(), HydeCoreExtension::getPageClasses()),
            array_map(fn ($class) => [$class], HydeCoreExtension::getPageClasses())
        );
    }
}
