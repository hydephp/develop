<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Services;

use Hyde\Facades\Features;
use Hyde\Framework\Features\XmlGenerators\RssFeedGenerator;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Features\XmlGenerators\RssFeedGenerator
 */
class RssFeedServiceTest extends TestCase
{
    public function test_service_instantiates_xml_element()
    {
        $service = new RssFeedGenerator();
        $this->assertInstanceOf('SimpleXMLElement', $service->getXmlElement());
    }

    public function test_xml_root_element_is_set_to_rss_2_0()
    {
        $service = new RssFeedGenerator();
        $this->assertEquals('rss', $service->getXmlElement()->getName());
        $this->assertEquals('2.0', $service->getXmlElement()->attributes()->version);
    }

    public function test_xml_element_has_channel_element()
    {
        $service = new RssFeedGenerator();
        $this->assertObjectHasAttribute('channel', $service->getXmlElement());
    }

    public function test_xml_channel_element_has_required_elements()
    {
        config(['site.name' => 'Test Blog']);
        config(['site.url' => 'https://example.com']);

        $service = new RssFeedGenerator();
        $this->assertObjectHasAttribute('title', $service->getXmlElement()->channel);
        $this->assertObjectHasAttribute('link', $service->getXmlElement()->channel);
        $this->assertObjectHasAttribute('description', $service->getXmlElement()->channel);

        $this->assertEquals('Test Blog', $service->getXmlElement()->channel->title);
        $this->assertEquals('https://example.com', $service->getXmlElement()->channel->link);
        $this->assertEquals('Test Blog RSS Feed', $service->getXmlElement()->channel->description);
    }

    public function test_xml_channel_element_has_additional_elements()
    {
        config(['site.url' => 'https://example.com']);

        $service = new RssFeedGenerator();
        $this->assertObjectHasAttribute('link', $service->getXmlElement()->channel);
        $this->assertEquals('https://example.com', $service->getXmlElement()->channel->link);
        $this->assertEquals('https://example.com/feed.xml',
            $service->getXmlElement()->channel->children('atom', true)->link->attributes()->href);

        $this->assertObjectHasAttribute('language', $service->getXmlElement()->channel);
        $this->assertObjectHasAttribute('generator', $service->getXmlElement()->channel);
        $this->assertObjectHasAttribute('lastBuildDate', $service->getXmlElement()->channel);
    }

    public function test_xml_channel_data_can_be_customized()
    {
        config(['site.name' => 'Foo']);
        config(['site.url' => 'https://blog.foo.com/bar']);
        config(['hyde.rss_description' => 'Foo is a web log about stuff']);

        $service = new RssFeedGenerator();
        $this->assertEquals('Foo', $service->getXmlElement()->channel->title);
        $this->assertEquals('https://blog.foo.com/bar', $service->getXmlElement()->channel->link);
        $this->assertEquals('Foo is a web log about stuff', $service->getXmlElement()->channel->description);
    }

    public function test_markdown_blog_posts_are_added_to_rss_feed_through_autodiscovery()
    {
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

        config(['site.url' => 'https://example.com']);

        file_put_contents(Hyde::path('_media/rss-test.jpg'), 'statData'); // 8 bytes to test stat gets file length

        $service = new RssFeedGenerator();
        $service->generate();
        $this->assertCount(1, $service->getXmlElement()->channel->item);

        $item = $service->getXmlElement()->channel->item[0];
        $this->assertEquals('RSS', $item->title);
        $this->assertEquals('RSS description', $item->description);
        $this->assertEquals('https://example.com/posts/rss.html', $item->link);
        $this->assertEquals(date(DATE_RSS, strtotime('2022-05-19T10:15:30+00:00')), $item->pubDate);
        $this->assertEquals('Hyde', $item->children('dc', true)->creator);
        $this->assertEquals('test', $item->category);

        $this->assertObjectHasAttribute('enclosure', $item);
        $this->assertEquals('https://example.com/media/rss-test.jpg', $item->enclosure->attributes()->url);
        $this->assertEquals('image/jpeg', $item->enclosure->attributes()->type);
        $this->assertEquals('8', $item->enclosure->attributes()->length);

        unlink(Hyde::path('_posts/rss.md'));
        unlink(Hyde::path('_media/rss-test.jpg'));
    }

    public function test_get_xml_method_returns_xml_string()
    {
        $service = new RssFeedGenerator();
        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', ($service->getXml()));
    }

    public function test_generate_feed_helper_returns_xml_string()
    {
        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', (RssFeedGenerator::make()));
    }

    public function test_can_generate_feed_helper_returns_true_if_hyde_has_base_url()
    {
        config(['site.url' => 'foo']);
        $this->file('_posts/foo.md');
        $this->assertTrue(Features::rss());
    }

    public function test_can_generate_feed_helper_returns_false_if_hyde_does_not_have_base_url()
    {
        config(['site.url' => '']);
        $this->file('_posts/foo.md');
        $this->assertFalse(Features::rss());
    }

    public function test_can_generate_feed_helper_returns_false_if_feeds_are_disabled_in_config()
    {
        config(['site.url' => 'foo']);
        config(['hyde.generate_rss_feed' => false]);
        $this->assertFalse(Features::rss());
    }
}
