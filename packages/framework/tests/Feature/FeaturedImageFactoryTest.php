<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Factories\FeaturedImageFactory;
use Hyde\Framework\Features\Blogging\Models\FeaturedImage;
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
        $image = $this->makeFromArray([
            'image.path' => 'path',
        ]);

        $this->assertInstanceOf(LocalFeaturedImage::class, $image);
        $this->assertSame('media/path', $image->getSource());
    }

    public function testMakeMethodCreatesRemoteImageWhenUrlIsSet()
    {
        $image = $this->makeFromArray([
            'image.url' => 'url',
        ]);

        $this->assertInstanceOf(RemoteFeaturedImage::class, $image);
        $this->assertSame('url', $image->getSource());
    }

    public function testMakeMethodCreatesRemoteImageWhenBothUrlAndPathIsSet()
    {
        $image = $this->makeFromArray([
            'image.url' => 'url',
            'image.path' => 'path',
        ]);

        $this->assertInstanceOf(RemoteFeaturedImage::class, $image);
        $this->assertSame('url', $image->getSource());
    }

    public function testMakeMethodThrowsExceptionIfNoPathInformationIsSet()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No featured image source was found');

        $this->makeFromArray([]);
    }

    public function testMakeMethodCanCreateImageFromJustString()
    {
        $image = $this->makeFromArray([
            'image' => 'foo',
        ]);

        $this->assertInstanceOf(LocalFeaturedImage::class, $image);
        $this->assertSame('media/foo', $image->getSource());
    }

    public function testMakeMethodCanCreateImageFromJustStringWithUrl()
    {
        $image = $this->makeFromArray([
            'image' => 'https://example.com/foo',
        ]);

        $this->assertInstanceOf(RemoteFeaturedImage::class, $image);
        $this->assertSame('https://example.com/foo', $image->getSource());
    }

    public function testImagePathsAreNormalized()
    {
        $this->assertSourceIsSame('media/foo', ['image' => 'foo']);
        $this->assertSourceIsSame('media/foo', ['image' => 'media/foo']);
        $this->assertSourceIsSame('media/foo', ['image' => '_media/foo']);

        $this->assertSourceIsSame('media/foo', ['image' => ['path' => 'foo']]);
        $this->assertSourceIsSame('media/foo', ['image' => ['path' => 'media/foo']]);
        $this->assertSourceIsSame('media/foo', ['image' => ['path' => '_media/foo']]);
    }

    protected function makeFromArray(array $matter): FeaturedImage
    {
        return FeaturedImageFactory::make(new FrontMatter($matter));
    }

    protected function assertSourceIsSame(string $expected, array $matter): void
    {
        $this->assertSame($expected, $this->makeFromArray($matter)->getSource());
    }
}
