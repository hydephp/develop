<?php

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Concerns\HasFeaturedImage;
use Hyde\Framework\Models\Image;
use Hyde\Testing\TestCase;

/**
 * Class HasFeaturedImageTest.
 *
 * @covers \Hyde\Framework\Concerns\HasFeaturedImage
 */
class HasFeaturedImageTest extends TestCase
{
    use HasFeaturedImage;

    protected array $matter;

    public function test_it_can_create_a_new_image_instance_from_a_string()
    {
        $this->matter = [
            'image' => 'https://example.com/image.jpg',
        ];

        $this->constructFeaturedImage();
        $this->assertInstanceOf(Image::class, $this->image);
        $this->assertEquals('https://example.com/image.jpg', $this->image->uri);
    }

    public function test_it_can_create_a_new_image_instance_from_an_array()
    {
        $this->matter = [
            'image' => [
                'uri' => 'https://example.com/image.jpg',
            ],
        ];

        $this->constructFeaturedImage();
        $this->assertInstanceOf(Image::class, $this->image);
        $this->assertEquals('https://example.com/image.jpg', $this->image->uri);
    }

    public function test_construct_base_image_sets_the_source_to_the_image_uri_when_supplied_path_is_an_uri()
    {
        $image = $this->constructBaseImage('https://example.com/image.jpg');
        $this->assertEquals('https://example.com/image.jpg', $image->getSource());
    }

    public function test_construct_base_image_sets_the_source_to_the_image_path_when_supplied_path_is_a_local_path()
    {
        $image = $this->constructBaseImage('/path/to/image.jpg');
        $this->assertEquals('/path/to/image.jpg', $image->getSource());
    }

    public function test_construct_base_image_returns_an_image_instance_created_from_a_string()
    {
        $this->assertInstanceOf(Image::class, $this->constructBaseImage(''));
    }

    public function test_construct_full_image_returns_an_image_instance_created_from_an_array()
    {
        $this->assertInstanceOf(Image::class, $this->constructFullImage([]));
    }
}
