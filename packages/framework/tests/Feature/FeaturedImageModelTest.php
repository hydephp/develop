<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use BadMethodCallException;
use function file_put_contents;
use Hyde\Framework\Features\Blogging\Models\FeaturedImage;
use Hyde\Pages\MarkdownPost;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Http;
use function str_replace;
use function strip_tags;
use function unlink;

/**
 * @covers \Hyde\Framework\Features\Blogging\Models\FeaturedImage
 */
class FeaturedImageModelTest extends TestCase
{
    public function test_can_construct_new_image()
    {
        $image = new FeaturedImage();
        $this->assertInstanceOf(FeaturedImage::class, $image);
    }

    public function test_make_can_create_an_image_based_on_string()
    {
        $image = FeaturedImage::make('foo');
        $this->assertInstanceOf(FeaturedImage::class, $image);
        $this->assertEquals('foo', $image->path);
    }

    public function test_make_can_create_an_image_based_on_array()
    {
        $image = FeaturedImage::make([
            'path' => 'foo',
            'title' => 'bar',
        ]);
        $this->assertInstanceOf(FeaturedImage::class, $image);
        $this->assertEquals('foo', $image->path);
        $this->assertEquals('bar', $image->title);
    }

    public function test_image_path_is_normalized_to_never_begin_with_media_prefix()
    {
        $image = FeaturedImage::make('foo');
        $this->assertSame('foo', $image->path);

        $image = FeaturedImage::make('_media/foo');
        $this->assertSame('foo', $image->path);

        $image = FeaturedImage::make('_media/foo');
        $this->assertSame('foo', $image->path);
    }

    public function test_image_source_path_is_normalized_to_always_begin_with_media_prefix()
    {
        $image = FeaturedImage::make('foo');
        $this->assertSame('_media/foo', $image->getSourcePath());

        $image = FeaturedImage::make('_media/foo');
        $this->assertSame('_media/foo', $image->getSourcePath());

        $image = FeaturedImage::make('_media/foo');
        $this->assertSame('_media/foo', $image->getSourcePath());
    }

    public function test_from_source_automatically_assigns_proper_property_depending_on_if_the_string_is_remote()
    {
        $image = FeaturedImage::fromSource('https://example.com/image.jpg');
        $this->assertInstanceOf(FeaturedImage::class, $image);
        $this->assertEquals('https://example.com/image.jpg', $image->url);

        $image = FeaturedImage::fromSource('image.jpg');
        $this->assertInstanceOf(FeaturedImage::class, $image);
        $this->assertEquals('image.jpg', $image->path);
    }

    public function test_array_data_can_be_used_to_initialize_properties_in_constructor()
    {
        $data = [
            'path' => 'image.jpg',
            'url' => 'https://example.com/image.jpg',
            'description' => 'This is an image',
            'title' => 'FeaturedImage Title',
        ];

        $image = new FeaturedImage($data);

        $this->assertEquals($data['path'], $image->path);
        $this->assertEquals($data['url'], $image->url);
        $this->assertEquals($data['description'], $image->description);
        $this->assertEquals($data['title'], $image->title);
    }

    public function test_get_source_method_returns_url_when_both_url_and_path_is_set()
    {
        $image = new FeaturedImage();
        $image->url = 'https://example.com/image.jpg';
        $image->path = 'image.jpg';

        $this->assertEquals('https://example.com/image.jpg', $image->getSource());
    }

    public function test_get_source_method_returns_path_when_only_path_is_set()
    {
        $image = new FeaturedImage();
        $image->path = 'image.jpg';

        $this->assertEquals('image.jpg', $image->getSource());
    }

    public function test_get_source_method_throws_exception_when_no_source_is_set()
    {
        $image = new FeaturedImage();

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Attempting to get source from Image that has no source.');
        $image->getSource();
    }

    public function test_get_source_method_does_not_throw_exception_when_path_is_set()
    {
        $image = new FeaturedImage();
        $image->path = 'image.jpg';
        $this->assertEquals('image.jpg', $image->getSource());
    }

    public function test_get_source_method_does_not_throw_exception_when_url_is_set()
    {
        $image = new FeaturedImage();
        $image->url = 'https://example.com/image.jpg';
        $this->assertEquals('https://example.com/image.jpg', $image->getSource());
    }

    public function test_get_metadata_array()
    {
        $image = new FeaturedImage([
            'description' => 'foo',
            'title' => 'bar',
            'path' => 'image.jpg',
        ]);

        $this->assertEquals([
            'text' => 'foo',
            'name' => 'bar',
            'url' => 'media/image.jpg',
            'contentUrl' => 'media/image.jpg',
        ], $image->getMetadataArray());
    }

    public function test_get_metadata_array_with_remote_url()
    {
        $image = new FeaturedImage([
            'url' => 'https://foo/bar',
        ]);

        $this->assertEquals([
            'url' => 'https://foo/bar',
            'contentUrl' => 'https://foo/bar',
        ], $image->getMetadataArray());
    }

    public function test_get_metadata_array_with_local_path()
    {
        $image = new FeaturedImage([
            'path' => 'foo.png',
        ]);

        $this->assertEquals([
            'url' => 'media/foo.png',
            'contentUrl' => 'media/foo.png',
        ], $image->getMetadataArray());
    }

    public function test_get_metadata_array_with_local_path_when_on_nested_page()
    {
        $this->mockCurrentPage('foo/bar');
        $image = new FeaturedImage([
            'path' => 'foo.png',
        ]);

        $this->assertEquals([
            'url' => '../media/foo.png',
            'contentUrl' => '../media/foo.png',
        ], $image->getMetadataArray());
    }

    public function test_get_link_resolves_remote_paths()
    {
        $image = new FeaturedImage([
            'url' => 'https://example.com/image.jpg',
        ]);

        $this->assertEquals('https://example.com/image.jpg', $image->getLink());
    }

    public function test_get_link_resolves_local_paths()
    {
        $image = new FeaturedImage([
            'path' => 'image.jpg',
        ]);

        $this->assertEquals('media/image.jpg', $image->getLink());
    }

    public function test_get_link_resolves_local_paths_when_on_nested_page()
    {
        $image = new FeaturedImage([
            'path' => 'image.jpg',
        ]);

        $this->mockCurrentPage('foo/bar');
        $this->assertEquals('../media/image.jpg', $image->getLink());
    }

    public function test_local_path_is_normalized_to_the_media_directory()
    {
        $this->assertEquals('image.jpg', (new FeaturedImage([
            'path' => 'image.jpg',
        ]))->path);

        $this->assertEquals('image.jpg', (new FeaturedImage([
            'path' => '_media/image.jpg',
        ]))->path);

        $this->assertEquals('image.jpg', (new FeaturedImage([
            'path' => 'media/image.jpg',
        ]))->path);
    }

    public function test_to_string_returns_the_image_source()
    {
        $this->assertEquals('https://example.com/image.jpg', (string) (new FeaturedImage([
            'url' => 'https://example.com/image.jpg',
        ])));

        $this->assertEquals('media/image.jpg', (string) (new FeaturedImage([
            'path' => 'image.jpg',
        ])));
    }

    public function test_to_string_returns_the_image_source_for_nested_pages()
    {
        $this->mockCurrentPage('foo/bar');
        $this->assertEquals('../media/image.jpg', (string) (new FeaturedImage([
            'path' => 'image.jpg',
        ])));
    }

    public function test_it_can_find_the_content_length_for_a_local_image_stored_in_the_media_directory()
    {
        $image = new FeaturedImage(['path' => 'image.jpg']);
        file_put_contents($image->getSourcePath(), '16bytelongstring');

        $this->assertEquals(
            16, $image->getContentLength()
        );

        unlink($image->getSourcePath());
    }

    public function test_it_can_find_the_content_length_for_a_remote_image()
    {
        Http::fake(function () {
            return Http::response(null, 200, [
                'Content-Length' => 16,
            ]);
        });

        $image = new FeaturedImage();
        $image->url = 'https://hyde.test/static/image.png';

        $this->assertEquals(
            16, $image->getContentLength()
        );
    }

    public function test_it_returns_0_if_local_image_is_missing()
    {
        $image = new FeaturedImage();
        $image->path = '_media/image.jpg';

        $this->assertEquals(
            0, $image->getContentLength()
        );
    }

    public function test_it_returns_0_if_remote_image_is_missing()
    {
        Http::fake(function () {
            return Http::response(null, 404);
        });

        $image = new FeaturedImage();
        $image->url = 'https://hyde.test/static/image.png';

        $this->assertEquals(
            0, $image->getContentLength()
        );
    }

}
