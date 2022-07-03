<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Models\Image;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Models\Image
 */
class ImageModelTest extends TestCase
{
    public function test_can_construct_new_image()
    {
        $image = new Image();
        $this->assertInstanceOf(Image::class, $image);
    }

    public function test_array_data_can_be_used_to_initialize_properties_in_constructor()
    {
        $data = [
            'path' => 'path/to/image.jpg',
            'uri' => 'https://example.com/image.jpg',
            'description' => 'This is an image',
            'title' => 'Image Title',
        ];

        $image = new Image($data);

        $this->assertEquals($data['path'], $image->path);
        $this->assertEquals($data['uri'], $image->uri);
        $this->assertEquals($data['description'], $image->description);
        $this->assertEquals($data['title'], $image->title);
    }

    public function test_get_source_method_returns_uri_when_both_uri_and_path_is_set()
    {
        $image = new Image();
        $image->uri = 'https://example.com/image.jpg';
        $image->path = 'path/to/image.jpg';

        $this->assertEquals('https://example.com/image.jpg', $image->getSource());
    }

    public function test_get_source_method_returns_path_when_only_path_is_set()
    {
        $image = new Image();
        $image->path = 'path/to/image.jpg';

        $this->assertEquals('path/to/image.jpg', $image->getSource());
    }

    public function test_get_source_method_returns_null_when_no_source_is_set()
    {
        $image = new Image();

        $this->assertNull($image->getSource());
    }

    public function test_get_image_author_attribution_string_method()
    {
        $image = new Image([
            'author' => 'John Doe',
            'credit' => 'https://example.com/',
        ]);
        $string = $image->getImageAuthorAttributionString();
        $this->assertStringContainsString('itemprop="creator"', $string);
        $this->assertStringContainsString('itemprop="url"', $string);
        $this->assertStringContainsString('itemtype="http://schema.org/Person"', $string);
        $this->assertStringContainsString('<span itemprop="name">John Doe</span>', $string);
        $this->assertStringContainsString('<a href="https://example.com/"', $string);

        $image = new Image(['author' => 'John Doe']);
        $string = $image->getImageAuthorAttributionString();
        $this->assertStringContainsString('itemprop="creator"', $string);
        $this->assertStringContainsString('itemtype="http://schema.org/Person"', $string);
        $this->assertStringContainsString('<span itemprop="name">John Doe</span>', $string);

        $image = new Image();
        $this->assertNull($image->getImageAuthorAttributionString());
    }

    public function test_get_copyright_string()
    {
        $image = new Image(['copyright' => 'foo']);
        $this->assertEquals('<span itemprop="copyrightNotice">foo</span>', $image->getCopyrightString());

        $image = new Image();
        $this->assertNull($image->getCopyrightString());
    }

    public function test_get_license_string()
    {
        $image = new Image([
            'license' => 'foo',
            'licenseUrl' => 'https://example.com/bar.html',
        ]);
        $this->assertEquals('<a href="https://example.com/bar.html" rel="license nofollow noopener" '.
                'itemprop="license">foo</a>', $image->getLicenseString());

        $image = new Image(['license' => 'foo']);
        $this->assertEquals('<span itemprop="license">foo</span>', $image->getLicenseString());

        $image = new Image(['licenseUrl' => 'https://example.com/bar.html']);
        $this->assertNull($image->getLicenseString());

        $image = new Image();
        $this->assertNull($image->getLicenseString());
    }

    public function test_get_fluent_attribution_method()
    {
        $image = new Image(['author' => 'John Doe']);
        $string = $image->getFluentAttribution();

        $this->assertStringContainsString('Image by ', $string);

        $image = new Image(['copyright' => 'foo']);
        $string = $image->getFluentAttribution();

        $this->assertStringContainsString('<span itemprop="copyrightNotice">foo</span>', $string);

        $image = new Image(['license' => 'foo']);

        $string = $image->getFluentAttribution();
        $this->assertStringContainsString('License <span itemprop="license">foo</span>', $string);

        $image = new Image();
        $this->assertEquals('', $image->getFluentAttribution());
    }

    public function test_get_metadata_array()
    {
        $image = new Image([
            'description' => 'foo',
            'title' => 'bar',
        ]);

        $this->assertEquals([
            'text' => 'foo',
            'name' => 'bar',
            'url' => null,
            'contentUrl' => null,
        ], $image->getMetadataArray());
    }


    public function test_get_link_resolves_remote_paths()
    {
        $image = new Image([
            'uri' => 'https://example.com/image.jpg',
        ]);

        $this->assertEquals('https://example.com/image.jpg', $image->getLink());
    }

    public function test_get_link_resolves_local_paths()
    {
        $image = new Image([
            'path' => 'image.jpg',
        ]);

        $this->assertEquals('media/image.jpg', $image->getLink());
    }

    public function test_get_link_resolves_local_paths_when_on_nested_page()
    {
        $image = new Image([
            'path' => 'image.jpg',
        ]);

        $this->assertEquals('../media/image.jpg', $image->getLink('foo/bar'));
    }

}
