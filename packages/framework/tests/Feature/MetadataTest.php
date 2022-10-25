<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Concerns\HydePage;
use Hyde\Framework\Helpers\Meta;
use Hyde\Framework\Models\Metadata\LinkItem;
use Hyde\Framework\Models\Metadata\MetadataItem;
use Hyde\Framework\Models\Metadata\OpenGraphItem;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Framework\Modules\Metadata\MetadataBag;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Modules\Metadata\MetadataBag
 * @covers \Hyde\Framework\Models\Metadata\LinkItem
 * @covers \Hyde\Framework\Models\Metadata\MetadataItem
 * @covers \Hyde\Framework\Models\Metadata\OpenGraphItem
 */
class MetadataTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['site.url' => null]);
        config(['hyde.meta' => []]);
        config(['hyde.generate_rss_feed' => false]);
        config(['site.generate_sitemap' => false]);
    }

    protected function assertPageHasMetadata(HydePage $page, string $metadata)
    {
        $this->assertStringContainsString(
            $metadata,
            $page->metadata->render()
        );
    }

    protected function assertPageDoesNotHaveMetadata(HydePage $page, string $metadata)
    {
        $this->assertStringNotContainsString(
            $metadata,
            $page->metadata->render()
        );
    }

    public function test_metadata_object_is_generated_automatically()
    {
        $page = new MarkdownPage();

        $this->assertNotNull($page->metadata);
        $this->assertInstanceOf(MetadataBag::class, $page->metadata);
        $this->assertEquals([], $page->metadata->get());
    }

    public function test_link_item_model()
    {
        $item = new LinkItem('rel', 'href');
        $this->assertEquals('rel', $item->uniqueKey());
        $this->assertEquals('<link rel="rel" href="href">', (string) $item);

        $item = new LinkItem('rel', 'href', ['attr' => 'value']);
        $this->assertEquals('<link rel="rel" href="href" attr="value">', (string) $item);
    }

    public function test_metadata_item_model()
    {
        $item = new MetadataItem('name', 'content');
        $this->assertEquals('name', $item->uniqueKey());
        $this->assertEquals('<meta name="name" content="content">', (string) $item);
    }

    public function test_open_graph_item_model()
    {
        $item = new OpenGraphItem('property', 'content');
        $this->assertEquals('property', $item->uniqueKey());
        $this->assertEquals('<meta property="og:property" content="content">', (string) $item);

        $item = new OpenGraphItem('og:property', 'content');
        $this->assertEquals('<meta property="og:property" content="content">', (string) $item);
    }

    public function test_link_item_can_be_added()
    {
        $page = new MarkdownPage();
        $page->metadata->add(Meta::link('foo', 'bar'));

        $this->assertEquals([
            'foo' => Meta::link('foo', 'bar'),
        ], $page->metadata->links);
    }

    public function test_metadata_item_can_be_added()
    {
        $page = new MarkdownPage();
        $page->metadata->add(Meta::name('foo', 'bar'));

        $this->assertEquals([
            'foo' => Meta::name('foo', 'bar'),
        ], $page->metadata->metadata);
    }

    public function test_open_graph_item_can_be_added()
    {
        $page = new MarkdownPage();
        $page->metadata->add(Meta::property('foo', 'bar'));

        $this->assertEquals([
            'foo' => Meta::property('foo', 'bar'),
        ], $page->metadata->properties);
    }

    public function test_generic_item_can_be_added()
    {
        $page = new MarkdownPage();
        $page->metadata->add('foo');

        $this->assertEquals([
            'foo',
        ], $page->metadata->generics);
    }

    public function test_multiple_items_can_be_accessed_with_get_method()
    {
        $page = new MarkdownPage();
        $page->metadata->add(Meta::link('foo', 'bar'));
        $page->metadata->add(Meta::name('foo', 'bar'));
        $page->metadata->add(Meta::property('foo', 'bar'));
        $page->metadata->add('foo');

        $this->assertEquals([
            'links:foo' => Meta::link('foo', 'bar'),
            'metadata:foo' => Meta::name('foo', 'bar'),
            'properties:foo' => Meta::property('foo', 'bar'),
            'generics:0' => 'foo',
        ], $page->metadata->get());
    }

    public function test_multiple_items_of_same_key_and_type_only_keeps_latest()
    {
        $page = new MarkdownPage();
        $page->metadata->add(Meta::link('foo', 'bar'));
        $page->metadata->add(Meta::link('foo', 'baz'));

        $this->assertEquals([
            'foo' => Meta::link('foo', 'baz'),
        ], $page->metadata->links);
    }

    public function test_render_returns_html_string_of_imploded_metadata_arrays()
    {
        $page = new MarkdownPage();
        $page->metadata->add(Meta::link('foo', 'bar'));
        $page->metadata->add(Meta::name('foo', 'bar'));
        $page->metadata->add(Meta::property('foo', 'bar'));
        $page->metadata->add('foo');

        $this->assertEquals(implode("\n", [
            '<link rel="foo" href="bar">',
            '<meta name="foo" content="bar">',
            '<meta property="og:foo" content="bar">',
            'foo',
        ]),
        $page->metadata->render());
    }

    public function test_custom_metadata_overrides_config_defined_metadata()
    {
        config(['hyde.meta' => [
            Meta::name('foo', 'bar'),
        ]]);
        $page = new MarkdownPage();
        $page->metadata->add(Meta::name('foo', 'baz'));
        $this->assertEquals([
            'metadata:foo' => Meta::name('foo', 'baz'),
        ], $page->metadata->get());
    }

    public function test_dynamic_metadata_overrides_config_defined_metadata()
    {
        config(['hyde.meta' => [
            Meta::name('twitter:title', 'bar'),
        ]]);
        $page = MarkdownPage::make(matter: ['title' => 'baz']);

        $this->assertEquals([
            'metadata:twitter:title' => Meta::name('twitter:title', 'HydePHP - baz'),
            'properties:title' => Meta::property('title', 'HydePHP - baz'),
        ], $page->metadata->get());
    }

    public function test_does_not_add_canonical_link_when_base_url_is_not_set()
    {
        config(['site.url' => null]);
        $page = MarkdownPage::make('bar');

        $this->assertStringNotContainsString('<link rel="canonical"', $page->metadata->render());
    }

    public function test_does_not_add_canonical_link_when_identifier_is_not_set()
    {
        config(['site.url' => 'foo']);
        $page = MarkdownPage::make();

        $this->assertStringNotContainsString('<link rel="canonical"', $page->metadata->render());
    }

    public function test_adds_canonical_link_when_base_url_and_identifier_is_set()
    {
        config(['site.url' => 'foo']);
        $page = MarkdownPage::make('bar');

        $this->assertStringContainsString('<link rel="canonical" href="foo/bar.html">', $page->metadata->render());
    }

    public function test_canonical_link_uses_clean_url_setting()
    {
        config(['site.url' => 'foo']);
        config(['site.pretty_urls' => true]);
        $page = MarkdownPage::make('bar');

        $this->assertStringContainsString('<link rel="canonical" href="foo/bar">', $page->metadata->render());
    }

    public function test_can_override_canonical_link_with_front_matter()
    {
        config(['site.url' => 'foo']);
        $page = MarkdownPage::make('bar', [
            'canonicalUrl' => 'canonical',
        ]);
        $this->assertStringContainsString('<link rel="canonical" href="canonical">', $page->metadata->render());
    }

    public function test_adds_twitter_and_open_graph_title_when_title_is_set()
    {
        $page = MarkdownPage::make(matter: ['title' => 'Foo Bar']);

        $this->assertEquals(
            '<meta name="twitter:title" content="HydePHP - Foo Bar">'."\n".
            '<meta property="og:title" content="HydePHP - Foo Bar">',
            $page->metadata->render()
        );
    }

    public function test_does_not_add_twitter_and_open_graph_title_when_no_title_is_set()
    {
        $page = MarkdownPage::make(matter: ['title' => null]);

        $this->assertEquals('',
            $page->metadata->render()
        );
    }

    public function test_adds_description_when_description_is_set_in_post()
    {
        $page = MarkdownPost::make(matter: ['description' => 'My Description']);
        $this->assertPageHasMetadata($page, '<meta name="description" content="My Description">');
    }

    public function test_does_not_add_description_when_description_is_not_set_in_post()
    {
        $page = new MarkdownPost();
        $this->assertPageDoesNotHaveMetadata($page, '<meta name="description" content="My Description">');
    }

    public function test_adds_author_when_author_is_set_in_post()
    {
        $page = MarkdownPost::make(matter: ['author' => 'My Author']);
        $this->assertPageHasMetadata($page, '<meta name="author" content="My Author">');
    }

    public function test_does_not_add_author_when_author_is_not_set_in_post()
    {
        $page = new MarkdownPost();
        $this->assertPageDoesNotHaveMetadata($page, '<meta name="author" content="My Author">');
    }

    public function test_adds_keywords_when_category_is_set_in_post()
    {
        $page = MarkdownPost::make(matter: ['category' => 'My Category']);
        $this->assertPageHasMetadata($page, '<meta name="keywords" content="My Category">');
    }

    public function test_does_not_add_keywords_when_category_is_not_set_in_post()
    {
        $page = new MarkdownPost();
        $this->assertPageDoesNotHaveMetadata($page, '<meta name="keywords" content="My Category">');
    }

    public function test_adds_url_property_when_canonical_url_is_set_in_post()
    {
        $page = MarkdownPost::make(matter: ['canonicalUrl' => 'example.html']);
        $this->assertPageHasMetadata($page, '<meta property="og:url" content="example.html">');
    }

    public function test_does_not_add_url_property_when_canonical_url_is_not_set_in_post()
    {
        $page = new MarkdownPost();
        $this->assertPageDoesNotHaveMetadata($page, '<meta property="og:url" content="example.html">');
    }

    public function test_does_not_add_url_property_when_canonical_url_is_null()
    {
        $page = MarkdownPost::make(matter: ['canonicalUrl' => null]);
        $this->assertPageDoesNotHaveMetadata($page, '<meta property="og:url" content="example.html">');
    }

    public function test_adds_title_property_when_title_is_set_in_post()
    {
        $page = MarkdownPost::make(matter: ['title' => 'My Title']);
        $this->assertPageHasMetadata($page, '<meta property="og:title" content="HydePHP - My Title">');
    }

    public function test_does_not_add_title_property_when_title_is_not_set_in_post()
    {
        $page = new MarkdownPost();
        $this->assertPageDoesNotHaveMetadata($page, '<meta property="og:title"');
    }

    public function test_adds_published_time_property_when_date_is_set_in_post()
    {
        $page = MarkdownPost::make(matter: ['date' => '2022-01-01']);
        $this->assertPageHasMetadata($page, '<meta property="og:article:published_time" content="2022-01-01T00:00:00+00:00">');
    }

    public function test_does_not_add_published_time_property_when_date_is_not_set_in_post()
    {
        $page = new MarkdownPost();
        $this->assertPageDoesNotHaveMetadata($page, '<meta property="og:article:published_time" content="2022-01-01T00:00:00+00:00">');
    }

    public function test_adds_image_property_when_image_is_set_in_post()
    {
        $page = MarkdownPost::make(matter: ['image' => 'image.jpg']);
        $this->assertPageHasMetadata($page, '<meta property="og:image" content="../media/image.jpg">');
    }

    public function test_does_not_add_image_property_when_image_is_not_set_in_post()
    {
        $page = new MarkdownPost();
        $this->assertPageDoesNotHaveMetadata($page, '<meta property="og:image" content="media/image.jpg">');
    }

    public function test_adds_type_property_automatically()
    {
        $page = MarkdownPost::make();
        $this->assertPageHasMetadata($page, '<meta property="og:type" content="article">');
    }

    public function test_dynamic_post_meta_properties_returns_base_array_when_initialized_with_empty_front_matter()
    {
        $page = MarkdownPost::make();
        $this->assertEquals('<meta property="og:type" content="article">', $page->metadata->render());
    }

    public function test_dynamic_post_meta_properties_contains_image_metadata_when_featured_image_set_to_string()
    {
        $page = MarkdownPost::make(matter: [
            'image' => 'foo.jpg',
        ]);

        $this->assertPageHasMetadata($page, '<meta property="og:image" content="../media/foo.jpg">');
    }

    public function test_dynamic_post_meta_properties_contains_image_link_that_is_always_relative()
    {
        $page = MarkdownPost::make(matter: [
            'image' => 'foo.jpg',
        ]);

        $this->assertPageHasMetadata($page, '<meta property="og:image" content="../media/foo.jpg">');
    }

    public function test_dynamic_post_meta_properties_contains_image_metadata_when_featured_image_set_to_array_with_path()
    {
        $page = MarkdownPost::make(matter: [
            'image' => [
                'path' => 'foo.jpg',
            ],
        ]);

        $this->assertPageHasMetadata($page, '<meta property="og:image" content="../media/foo.jpg">');
    }

    public function test_dynamic_post_meta_properties_contains_image_metadata_when_featured_image_set_to_array_with_url()
    {
        $page = MarkdownPost::make(matter: [
            'image' => [
                'url' => 'https://example.com/foo.jpg',
            ],
        ]);

        $this->assertPageHasMetadata($page, '<meta property="og:image" content="https://example.com/foo.jpg">');
    }

    public function test_dynamic_post_author_returns_author_name_when_author_set_to_array_using_username()
    {
        $page = MarkdownPost::make(matter: [
            'author' => [
                'username' => 'username',
            ],
        ]);
        $this->assertPageHasMetadata($page, '<meta name="author" content="username">');
    }

    public function test_dynamic_post_author_returns_author_name_when_author_set_to_array_using_name()
    {
        $page = MarkdownPost::make(matter: [
            'author' => [
                'name' => 'Name',
            ],
        ]);

        $this->assertPageHasMetadata($page, '<meta name="author" content="Name">');
    }

    public function test_no_author_is_set_when_author_set_to_array_without_name_or_username()
    {
        $page = MarkdownPost::make(matter: [
            'author' => [],
        ]);

        $this->assertPageDoesNotHaveMetadata($page, '<meta name="author"');
    }
}
