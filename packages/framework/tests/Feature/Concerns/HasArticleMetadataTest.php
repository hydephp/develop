<?php

namespace Hyde\Framework\Testing\Feature\Concerns;

use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Framework\Models\Route;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Config;

/**
 * @covers \Hyde\Framework\Concerns\HasArticleMetadata
 *
 * @see \Hyde\Framework\Models\Metadata
 */
class HasArticleMetadataTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('site.url', null);
    }

    public function test_get_metadata_returns_empty_array_when_uninitialized()
    {
        $page = MarkdownPost::make(matter: ['description' => 'foo']);
        $this->assertEquals([], $page->getMetadata());
    }

    public function test_get_metadata_returns_valid_array_when_initialized()
    {
        $page = MarkdownPost::make(matter: [
            'description' => 'foo',
            'author' => 'bar',
            'category' => 'cat',
        ]);
        $page->constructMetadata();
        $this->assertEquals([
            'description' => 'foo',
            'author' => 'bar',
            'keywords' => 'cat',
        ], $page->getMetadata());
    }

    public function test_get_meta_properties_returns_empty_array_when_uninitialized()
    {
        $page = MarkdownPost::make();
        $this->assertEquals([], $page->getMetaProperties());
    }

    public function test_get_meta_properties_returns_base_array_when_initialized_with_empty_front_matter()
    {
        $page = MarkdownPost::make();
        $page->constructMetadata();
        $this->assertEquals(['og:type' => 'article'], $page->getMetaProperties());
    }

    // Note that this currently assumes that the object using it is a Blog Post.
    public function test_get_meta_properties_contains_og_url_when_uri_path_set()
    {
        Config::set('site.url', 'https://example.com/foo');
        $page = MarkdownPost::make('bar');
        $page->constructMetadata();

        $this->assertEquals([
            'og:type' => 'article',
            'og:url' => 'https://example.com/foo/posts/bar.html',
        ], $page->getMetaProperties());
    }

    public function test_get_meta_properties_contains_og_title_when_title_set()
    {
        $page = MarkdownPost::make(matter: [
            'title' => 'foo',
        ]);
        $page->constructMetadata();

        $this->assertEquals([
            'og:type' => 'article',
            'og:title' => 'foo',
        ], $page->getMetaProperties());
    }

    public function test_get_meta_properties_contains_og_article_date_published_when_date_set()
    {
        $page = MarkdownPost::make(matter: [
            'date' => '2022-01-01 12:00',
        ]);
        $page->constructMetadata();

        $this->assertEquals([
            'og:type' => 'article',
            'og:article:published_time' => '2022-01-01T12:00:00+00:00',
        ], $page->getMetaProperties());
    }

    public function test_get_meta_properties_contains_image_metadata_when_featured_image_set_to_string()
    {
        $page = MarkdownPost::make(matter: [
            'image' => 'foo.jpg',
        ]);
        $page->constructMetadata();

        $this->assertEquals([
            'og:type' => 'article',
            'og:image' => 'foo.jpg',
        ], $page->getMetaProperties());
    }

    public function test_get_meta_properties_contains_image_metadata_when_featured_image_set_to_array_with_path()
    {
        $page = MarkdownPost::make(matter: [
            'image' => [
                'path' => 'foo.jpg',
            ],
        ]);
        $page->constructMetadata();

        $this->assertEquals([
            'og:type' => 'article',
            'og:image' => 'foo.jpg',
        ], $page->getMetaProperties());
    }

    public function test_get_meta_properties_contains_image_metadata_when_featured_image_set_to_array_with_uri()
    {
        $page = MarkdownPost::make(matter: [
            'image' => [
                'uri' => 'foo.jpg',
            ],
        ]);
        $page->constructMetadata();

        $this->assertEquals([
            'og:type' => 'article',
            'og:image' => 'foo.jpg',
        ], $page->getMetaProperties());
    }

    public function test_get_author_returns_author_name_when_author_set_to_array_using_username()
    {
        $page = MarkdownPost::make(matter: [
            'author' => [
                'username' => 'foo',
            ],
        ]);
        $page->constructMetadata();

        $this->assertEquals([
            'author' => 'foo',
        ], $page->getMetadata());
    }

    public function test_get_author_returns_author_name_when_author_set_to_array_using_name()
    {
        $page = MarkdownPost::make(matter: [
            'author' => [
                'name' => 'foo',
            ],
        ]);
        $page->constructMetadata();

        $this->assertEquals([
            'author' => 'foo',
        ], $page->getMetadata());
    }

    public function test_get_author_returns_guest_when_author_set_to_array_without_name_or_username()
    {
        $page = MarkdownPost::make(matter: [
            'author' => [],
        ]);
        $page->constructMetadata();

        $this->assertEquals([
            'author' => 'Guest',
        ], $page->getMetadata());
    }
}
