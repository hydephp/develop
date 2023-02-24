<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Factories\HydePageDataFactory;
use Hyde\Framework\Features\Navigation\NavigationData;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Pages\InMemoryPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Testing\UnitTestCase;
use Illuminate\Config\Repository;
use Illuminate\Support\Facades\Config;

/**
 * @covers \Hyde\Framework\Factories\HydePageDataFactory
 */
class HydePageDataFactoryTest extends UnitTestCase
{
    public static function setUpBeforeClass(): void
    {
        self::needsKernel();
        self::mockConfig([
            'hyde' => [
                //
            ],
        ]);
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
        $this->assertSame(['title', 'canonicalUrl', 'navigation'], array_keys($this->factory()->toArray()));
    }

    public function testCanCreateTitleFromMatter()
    {
        $this->assertSame('Foo', $this->factory(['title' => 'Foo'])->toArray()['title']);
    }

    public function testCanCreateTitleFromMarkdown()
    {
        $this->assertSame('Foo', $this->factory(page: new MarkdownPage(markdown: '# Foo'))->toArray()['title']);
    }

    public function testTitlePrefersMatter()
    {
        $this->assertSame('Foo', $this->factory(page: new MarkdownPage(matter: ['title' => 'Foo'], markdown: '# Bar'))->toArray()['title']);
    }

    public function testTitleFallsBackToIdentifier()
    {
        $this->assertSame('Foo', $this->factory(page: new MarkdownPage('foo'))->toArray()['title']);
    }

    public function testTitleFallsBackToIdentifierBasename()
    {
        $this->assertSame('Bar', $this->factory(page: new MarkdownPage('foo/bar'))->toArray()['title']);
    }

    public function testCanCreateCanonicalUrlUsingBaseUrlFromConfig()
    {
        self::mockConfig([
            'hyde' => [
                'url' => 'https://example.com',
            ],
        ]);

        $this->assertSame('https://example.com/foo.html', $this->factory(page: new MarkdownPage('foo'))->toArray()['canonicalUrl']);
    }

    public function testCanCreateCanonicalUrlUsingBaseUrlFromConfigUsingPrettyUrls()
    {
        self::mockConfig([
            'hyde' => [
                'url' => 'https://example.com',
                'pretty_urls' => true,
            ],
        ]);

        $this->assertSame('https://example.com/foo', $this->factory(page: new MarkdownPage('foo'))->toArray()['canonicalUrl']);
    }

    public function testCanonicalUrlIsNullWhenNoBaseUrlIsSet()
    {
        $this->assertNull($this->factory(page: new MarkdownPage('foo'))->toArray()['canonicalUrl']);
    }

    public function testNavigationDataIsGeneratedByNavigationDataFactory()
    {
        $this->assertInstanceOf(NavigationData::class, $this->factory()->toArray()['navigation']);
    }

    protected static function mockConfig(array $items = []): void
    {
        app()->bind('config', function () use ($items) {
            return new Repository($items);
        });

        Config::swap(app('config'));
    }

    protected function factory(array $data = [], HydePage $page = null): HydePageDataFactory
    {
        return new HydePageDataFactory(($page ?? new InMemoryPage('', $data))->toCoreDataObject());
    }
}
