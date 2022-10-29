<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Factories\FeaturedImageFactory;
use Hyde\Framework\Features\Blogging\Models\LocalFeaturedImage;
use Hyde\Framework\Features\Blogging\Models\RemoteFeaturedImage;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Testing\TestCase;
use RuntimeException;

/**
 * @covers \Hyde\Framework\Factories\FeaturedImageFactory
 */
class FeaturedImageFactoryTest extends TestCase
{
    public function testWithDataFromSchema()
    {
        $array = [
            'image.path' => 'path',
            'image.url' => 'url',
            'image.description' => 'description',
            'image.title' => 'title',
            'image.copyright' => 'copyright',
            'image.license' => 'license',
            'image.licenseUrl' => 'licenseUrl',
            'image.author' => 'author',
            'image.attributionUrl' => 'attributionUrl',
        ];

        $expected = [
            'source' => 'url',
            'altText' => 'description',
            'titleText' => 'title',
            'authorName' => 'author',
            'authorUrl' => 'attributionUrl',
            'copyrightText' => 'copyright',
            'licenseName' => 'license',
            'licenseUrl' => 'licenseUrl',
        ];

        $factory = new FeaturedImageFactory(new FrontMatter($array));

        $this->assertSame($expected, $factory->toArray());
    }

    public function testMakeMethodCreatesLocalImageWhenPathIsSet()
    {
        $factory = FeaturedImageFactory::make(new FrontMatter([
            'image.path' => 'path',
        ]));

        $this->assertInstanceOf(LocalFeaturedImage::class, $factory);
    }

    public function testMakeMethodCreatesRemoteImageWhenUrlIsSet()
    {
        $factory = FeaturedImageFactory::make(new FrontMatter([
            'image.url' => 'url',
        ]));

        $this->assertInstanceOf(RemoteFeaturedImage::class, $factory);
    }

    public function testMakeMethodCreatesRemoteImageWhenBothUrlAndPathIsSet()
    {
        $factory = FeaturedImageFactory::make(new FrontMatter([
            'image.url' => 'url',
            'image.path' => 'path',
        ]));

        $this->assertInstanceOf(RemoteFeaturedImage::class, $factory);
    }

    public function testMakeMethodThrowsExceptionIfNoPathInformationIsSet()
    {
        $this->expectException(RuntimeException::class);

        FeaturedImageFactory::make(new FrontMatter([]));
    }

    public function testMakeMethodCanCreateImageFromJustString()
    {
        $factory = FeaturedImageFactory::make(new FrontMatter([
            'image' => 'foo',
        ]));

        $this->assertInstanceOf(RemoteFeaturedImage::class, $factory);
    }
}
