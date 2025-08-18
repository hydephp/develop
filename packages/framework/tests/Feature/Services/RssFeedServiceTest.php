<?php

/** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Services;

use SimpleXMLElement;
use Hyde\Facades\Features;
use Hyde\Facades\Filesystem;
use Hyde\Framework\Features\XmlGenerators\RssFeedGenerator;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Framework\Features\XmlGenerators\RssFeedGenerator::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Framework\Features\XmlGenerators\BaseXmlGenerator::class)]
class RssFeedServiceTest extends TestCase
{
    public function testServiceInstantiatesXmlElement()
    {
        $service = new RssFeedGenerator();
        $this->assertInstanceOf(SimpleXMLElement::class, $service->getXmlElement());
    }

    public function testXmlRootElementIsSetToRss20()
    {
        $service = new RssFeedGenerator();
        $this->assertSame('rss', $service->getXmlElement()->getName());
        $this->assertSame('2.0', $service->getXmlElement()->attributes()->version);
    }

    public function testXmlElementHasChannelElement()
    {
        $service = new RssFeedGenerator();
        $this->assertObjectHasProperty('channel', $service->getXmlElement());
    }

    public function testXmlChannelElementHasRequiredElements()
    {
        config(['hyde.name' => 'Test Blog']);
        $this->withSiteUrl();
        config(['hyde.rss.description' => 'Test Blog RSS Feed']);

        $service = new RssFeedGenerator();

        $this->assertObjectHasProperty('title', $service->getXmlElement()->channel);
        $this->assertObjectHasProperty('link', $service->getXmlElement()->channel);
        $this->assertObjectHasProperty('description', $service->getXmlElement()->channel);

        $this->assertSame('Test Blog', $service->getXmlElement()->channel->title);
        $this->assertSame('https://example.com', $service->getXmlElement()->channel->link);
        $this->assertSame('Test Blog RSS Feed', $service->getXmlElement()->channel->description);
    }

    public function testXmlChannelElementHasAdditionalElements()
    {
        $this->withSiteUrl();

        $service = new RssFeedGenerator();

        $this->assertObjectHasProperty('link', $service->getXmlElement()->channel);
        $this->assertSame('https://example.com', $service->getXmlElement()->channel->link);
        $this->assertSame('https://example.com/feed.xml',
            $service->getXmlElement()->channel->children('atom', true)->link->attributes()->href
        );

        $this->assertObjectHasProperty('language', $service->getXmlElement()->channel);
        $this->assertObjectHasProperty('generator', $service->getXmlElement()->channel);
        $this->assertObjectHasProperty('lastBuildDate', $service->getXmlElement()->channel);
    }

    public function testXmlChannelDataCanBeCustomized()
    {
        config(['hyde.name' => 'Foo']);
        config(['hyde.url' => 'https://blog.foo.com/bar']);
        config(['hyde.rss.description' => 'Foo is a web log about stuff']);

        $service = new RssFeedGenerator();
        $this->assertSame('Foo', $service->getXmlElement()->channel->title);
        $this->assertSame('https://blog.foo.com/bar', $service->getXmlElement()->channel->link);
        $this->assertSame('Foo is a web log about stuff', $service->getXmlElement()->channel->description);
    }

    public function testMarkdownBlogPostsAreAddedToRssFeedThroughAutodiscovery()
    {
        config(['hyde.cache_busting' => false]);

        file_put_contents(Hyde::path('_posts/rss.md'), <<<'MD'
            ---
            title: RSS
            author: Hyde
            date: "2022-05-19 10:15:30"
            description: RSS description
            category: test
            image: _media/rss-test.jpg
            ---

            # RSS Post

            Foo bar
            MD
        );

        $this->withSiteUrl();

        file_put_contents(Hyde::path('_media/rss-test.jpg'), 'statData'); // 8 bytes to test stat gets file length

        $service = new RssFeedGenerator();
        $service->generate();
        $this->assertCount(1, $service->getXmlElement()->channel->item);

        $item = $service->getXmlElement()->channel->item[0];

        $this->assertSame('RSS', $item->title);
        $this->assertSame('RSS description', $item->description);
        $this->assertSame('https://example.com/posts/rss.html', $item->link);

        $this->assertSame(date(DATE_RSS, strtotime('2022-05-19T10:15:30+00:00')), $item->pubDate);
        $this->assertSame('Hyde', $item->children('dc', true)->creator);
        $this->assertSame('test', $item->category);

        $this->assertObjectHasProperty('enclosure', $item);
        $this->assertSame('https://example.com/media/rss-test.jpg', $item->enclosure->attributes()->url);
        $this->assertSame('image/jpeg', $item->enclosure->attributes()->type);
        $this->assertSame('8', $item->enclosure->attributes()->length);

        Filesystem::unlink('_posts/rss.md');
        Filesystem::unlink('_media/rss-test.jpg');
    }

    public function testGetXmlMethodReturnsXmlString()
    {
        $service = new RssFeedGenerator();
        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $service->getXml());
    }

    public function testGenerateFeedHelperReturnsXmlString()
    {
        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', RssFeedGenerator::make());
    }

    public function testCanGenerateFeedHelperReturnsTrueIfHydeHasBaseUrl()
    {
        config(['hyde.url' => 'foo']);

        $this->file('_posts/foo.md');
        $this->assertTrue(Features::hasRss());
    }

    public function testCanGenerateFeedHelperReturnsFalseIfHydeDoesNotHaveBaseUrl()
    {
        $this->withoutSiteUrl();
        $this->file('_posts/foo.md');
        $this->assertFalse(Features::hasRss());
    }

    public function testCanGenerateFeedHelperReturnsFalseIfFeedsAreDisabledInConfig()
    {
        config(['hyde.url' => 'foo']);
        config(['hyde.rss.enabled' => false]);

        $this->assertFalse(Features::hasRss());
    }
}
