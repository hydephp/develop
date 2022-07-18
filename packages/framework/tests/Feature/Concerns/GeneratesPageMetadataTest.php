<?php

namespace Hyde\Framework\Testing\Feature\Concerns;

use Hyde\Framework\Concerns\GeneratesPageMetadata;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Config;

/**
 * @covers \Hyde\Framework\Concerns\GeneratesPageMetadata
 *
 * @see \Hyde\Framework\Models\Metadata
 */
class GeneratesPageMetadataTest extends TestCase
{
    use GeneratesPageMetadata;

    public array $matter;
    protected string $slug;

    protected bool $forceOpenGraph = true;

    protected function tearDown(): void
    {
        unset($this->metadata);
        unset($this->matter);

        parent::tearDown();
    }

    public function test_get_metadata_returns_empty_array_when_uninitialized()
    {
        $this->matter = ['description' => 'foo'];
        $this->assertEquals([], $this->getMetadata());
    }

    public function test_get_metadata_returns_valid_array_when_initialized()
    {
        $this->matter = [
            'description' => 'foo',
            'author' => 'bar',
            'category' => 'cat',
        ];
        $this->constructMetadata();
        $this->assertEquals([
            'description' => 'foo',
            'author' => 'bar',
            'keywords' => 'cat',
        ], $this->getMetadata());
    }

    public function test_get_meta_properties_returns_empty_array_when_uninitialized()
    {
        $this->assertEquals([], $this->getMetaProperties());
    }

    public function test_get_meta_properties_returns_base_array_when_initialized_with_empty_front_matter()
    {
        $this->constructMetadata();

        $this->assertEquals(['og:type' => 'article'], $this->getMetaProperties());
    }

    // Note that this currently assumes that the object using it is a Blog Post.
    public function test_get_meta_properties_contains_og_url_when_uri_path_set()
    {
        Config::set('site.site_url', 'https://example.com/foo');
        $this->slug = 'bar';
        $this->constructMetadata();

        $this->assertEquals([
            'og:type' => 'article',
            'og:url' => 'https://example.com/foo/posts/bar.html',
        ], $this->getMetaProperties());
    }

    public function test_get_meta_properties_contains_og_title_when_title_set()
    {
        $this->matter = [
            'title' => 'foo',
        ];
        $this->constructMetadata();

        $this->assertEquals([
            'og:type' => 'article',
            'og:title' => 'foo',
        ], $this->getMetaProperties());
    }

    public function test_get_meta_properties_contains_og_article_date_published_when_date_set()
    {
        $this->matter = [
            'date' => '2022-01-01 12:00',
        ];
        $this->constructMetadata();

        $this->assertEquals([
            'og:type' => 'article',
            'og:article:published_time' => '2022-01-01T12:00:00+00:00',
        ], $this->getMetaProperties());
    }

    public function test_get_meta_properties_contains_image_metadata_when_featured_image_set_to_string()
    {
        $this->matter = [
            'image' => 'foo.jpg',
        ];
        $this->constructMetadata();

        $this->assertEquals([
            'og:type' => 'article',
            'og:image' => 'foo.jpg',
        ], $this->getMetaProperties());
    }

    public function test_get_meta_properties_contains_image_metadata_when_featured_image_set_to_array_with_path()
    {
        $this->matter = [
            'image' => [
                'path' => 'foo.jpg',
            ],
        ];
        $this->constructMetadata();

        $this->assertEquals([
            'og:type' => 'article',
            'og:image' => 'foo.jpg',
        ], $this->getMetaProperties());
    }

    public function test_get_meta_properties_contains_image_metadata_when_featured_image_set_to_array_with_uri()
    {
        $this->matter = [
            'image' => [
                'uri' => 'foo.jpg',
            ],
        ];
        $this->constructMetadata();

        $this->assertEquals([
            'og:type' => 'article',
            'og:image' => 'foo.jpg',
        ], $this->getMetaProperties());
    }

    public function test_get_author_returns_author_name_when_author_set_to_array_using_username()
    {
        $this->matter = [
            'author' => [
                'username' => 'foo',
            ],
        ];
        $this->constructMetadata();

        $this->assertEquals([
            'author' => 'foo',
        ], $this->getMetadata());
    }

    public function test_get_author_returns_author_name_when_author_set_to_array_using_name()
    {
        $this->matter = [
            'author' => [
                'name' => 'foo',
            ],
        ];
        $this->constructMetadata();

        $this->assertEquals([
            'author' => 'foo',
        ], $this->getMetadata());
    }

    public function test_get_author_returns_guest_when_author_set_to_array_without_name_or_username()
    {
        $this->matter = [
            'author' => [],
        ];
        $this->constructMetadata();

        $this->assertEquals([
            'author' => 'Guest',
        ], $this->getMetadata());
    }
}
