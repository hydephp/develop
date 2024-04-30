<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Facades\Config;
use Hyde\Testing\TestCase;
use Hyde\Pages\MarkdownPage;
use PHPUnit\Framework\TestRunner;
use Hyde\Framework\Features\Navigation\FilenamePrefixNavigationHelper;
use Hyde\Framework\Features\Navigation\FilenamePrefixNavigationHelperTest;

/**
 * High level test for the feature that allows navigation items to be sorted by filename prefix.
 *
 * The feature can be disabled in the config. It also works within sidebar groups,
 * so that multiple groups can have the same prefix independent of other groups.
 *
 * @covers \Hyde\Framework\Features\Navigation\FilenamePrefixNavigationHelper
 * @covers \Hyde\Framework\Features\Navigation\MainNavigationMenu
 * @covers \Hyde\Framework\Features\Navigation\DocumentationSidebar
 */
class FilenamePrefixNavigationPriorityTest extends TestCase
{
    public function testEnabledReturnsTrueWhenEnabled()
    {
        $this->assertTrue(FilenamePrefixNavigationHelper::enabled());
    }

    public function testEnabledReturnsFalseWhenDisabled()
    {
        Config::set('hyde.filename_page_ordering', false);

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

    public function testSourceFilesHaveTheirNumericalPrefixTrimmedFromRouteKeys()
    {
        $this->file('_pages/01-home.md');

        $identifier = '01-home';

        // Assert it is discovered.
        $discovered = MarkdownPage::get($identifier);
        $this->assertNotNull($discovered, 'The page was not discovered.');

        // Assert it is parsable
        $parsed = MarkdownPage::parse($identifier);
        $this->assertNotNull($parsed, 'The page was not parsable.');

        // Sanity check
        $this->assertEquals($discovered, $parsed);

        $page = $discovered;

        // Assert identifier is the same.
        $this->assertSame($identifier, $page->getIdentifier());

        // Assert the route key is trimmed.
        $this->assertSame('home', $page->getRouteKey());

        // Assert route key dependents are trimmed.
        $this->assertSame('home.html', $page->getOutputPath());
    }

    public function testSourceFilesDoNotHaveTheirNumericalPrefixTrimmedFromRouteKeysWhenFeatureIsDisabled()
    {
        Config::set('hyde.filename_page_ordering', false);

        $this->file('_pages/01-home.md');

        $identifier = '01-home';

        // Assert it is discovered.
        $discovered = MarkdownPage::get($identifier);
        $this->assertNotNull($discovered, 'The page was not discovered.');

        // Assert it is parsable
        $parsed = MarkdownPage::parse($identifier);
        $this->assertNotNull($parsed, 'The page was not parsable.');

        // Sanity check
        $this->assertEquals($discovered, $parsed);

        $page = $discovered;

        // Assert identifier is the same.
        $this->assertSame($identifier, $page->getIdentifier());

        // Assert the route key is trimmed.
        $this->assertSame($identifier, $page->getRouteKey());

        // Assert route key dependents are trimmed.
        $this->assertSame("$identifier.html", $page->getOutputPath());
    }

    protected function fixtureFlatMain(): array
    {
        return [
            '01-home.md',
            '02-about.md',
            '03-contact.md',
        ];
    }

    protected function fixtureGroupedMain(): array
    {
        return [
            '01-home.md',
            '02-about.md',
            '03-contact.md',
            '04-api' => [
                '01-readme.md',
                '02-installation.md',
                '03-getting-started.md',
            ],
        ];
    }

    protected function fixtureFlatSidebar(): array
    {
        return [
            '01-readme.md',
            '02-installation.md',
            '03-getting-started.md',
        ];
    }

    protected function fixtureGroupedSidebar(): array
    {
        return [
            '01-readme.md',
            '02-installation.md',
            '03-getting-started.md',
            '04-introduction' => [
                '01-features.md',
                '02-extensions.md',
                '03-configuration.md',
            ],
            '05-advanced' => [
                '01-features.md',
                '02-extensions.md',
                '03-configuration.md',
            ],
        ];
    }

    protected function fixturePrefixSyntaxes(): array
    {
        return [
            [
                '1-foo.md',
                '2-bar.md',
                '3-baz.md',
            ], [
                '01-foo.md',
                '02-bar.md',
                '03-baz.md',
            ], [
                '001-foo.md',
                '002-bar.md',
                '003-baz.md',
            ],
        ];
    }

    public function fixtureFileExtensions(): array
    {
        return [
            '01-foo.md',
            '02-bar.html',
            '03-baz.blade.php',
        ];
    }
}
