<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Factories\HydePageDataFactory;
use Hyde\Framework\Features\Navigation\NavigationData;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Pages\InMemoryPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Framework\Factories\HydePageDataFactory
 */
class HydePageDataFactoryTest extends UnitTestCase
{
    public static function setUpBeforeClass(): void
    {
        self::needsKernel();
        self::mockConfig();
    }

    protected function tearDown(): void
    {
        self::mockConfig();

        parent::tearDown();
    }

    public function testCanConstruct()
    {
        $this->assertInstanceOf(HydePageDataFactory::class, $this->factory());
    }

    public function testToArrayContainsExpectedKeys()
    {
        $this->assertSame(['title', 'navigation'], array_keys($this->factory()->toArray()));
    }

    public function testCanCreateTitleFromMatter()
    {
        $this->assertSame('Foo', $this->factory(['title' => 'Foo'])->toArray()['title']);
    }

    public function testCanCreateTitleFromMarkdown()
    {
        $this->assertSame('Foo', $this->factoryFromPage(new MarkdownPage(markdown: '# Foo'))->toArray()['title']);
    }

    public function testTitlePrefersMatter()
    {
        $this->assertSame('Foo', $this->factoryFromPage(new MarkdownPage(matter: ['title' => 'Foo'], markdown: '# Bar'))->toArray()['title']);
    }

    public function testTitleFallsBackToIdentifier()
    {
        $this->assertSame('Foo', $this->factoryFromPage(new MarkdownPage('foo'))->toArray()['title']);
    }

    public function testTitleFallsBackToIdentifierBasename()
    {
        $this->assertSame('Bar', $this->factoryFromPage(new MarkdownPage('foo/bar'))->toArray()['title']);
    }

    public function testIndexPageTitlesCanBeCreatedFromParentIdentifierBasename()
    {
        $this->assertSame('Foo', $this->factoryFromPage(new MarkdownPage('foo/index'))->toArray()['title']);
    }

    public function testIndexPageTitlesCanBeCreatedFromNestedParentIdentifierBasename()
    {
        $this->assertSame('Bar', $this->factoryFromPage(new MarkdownPage('foo/bar/index'))->toArray()['title']);
    }

    public function testNavigationDataIsGeneratedByNavigationDataFactory()
    {
        $this->assertInstanceOf(NavigationData::class, $this->factory()->toArray()['navigation']);
    }

    protected function factory(array $data = []): HydePageDataFactory
    {
        return $this->factoryFromPage((new InMemoryPage('', $data)));
    }

    protected function factoryFromPage(HydePage $page): HydePageDataFactory
    {
        return new HydePageDataFactory(($page)->toCoreDataObject());
    }
}
