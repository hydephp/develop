<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Factories\FeaturedImageFactory;
use Hyde\Framework\Features\Blogging\Models\FeaturedImage;
use Hyde\Hyde;
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
            'image.source' => 'source',
            'image.altText' => 'description',
            'image.titleText' => 'title',
            'image.copyright' => 'copyright',
            'image.licenseName' => 'license',
            'image.licenseUrl' => 'licenseUrl',
            'image.authorName' => 'author',
            'image.authorUrl' => 'authorUrl',
        ];

        $expected = [
            'source' => 'source',
            'altText' => 'description',
            'titleText' => 'title',
            'authorName' => 'author',
            'authorUrl' => 'authorUrl',
            'copyrightText' => 'copyright',
            'licenseName' => 'license',
            'licenseUrl' => 'licenseUrl',
        ];

        $factory = new FeaturedImageFactory(new FrontMatter($array));

        $this->assertSame($expected, $factory->toArray());
    }

    public function testMakeMethodCreatesImageWhenPathIsSet()
    {
        $image = $this->makeFromArray([
            'image.source' => 'foo',
        ]);

        $this->assertInstanceOf(FeaturedImage::class, $image);
        $this->assertSame('media/foo', $image->getSource());
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

        $this->assertInstanceOf(FeaturedImage::class, $image);
        $this->assertSame('media/foo', $image->getSource());
    }

    public function testMakeMethodCanCreateImageFromJustStringWithUrl()
    {
        $image = $this->makeFromArray([
            'image' => 'https://example.com/foo',
        ]);

        $this->assertInstanceOf(FeaturedImage::class, $image);
        $this->assertSame('https://example.com/foo', $image->getSource());
    }

    public function testImagePathsAreNormalized()
    {
        $this->assertSourceIsSame('media/foo', ['image' => 'foo']);
        $this->assertSourceIsSame('media/foo', ['image' => 'media/foo']);
        $this->assertSourceIsSame('media/foo', ['image' => '_media/foo']);

        $this->assertSourceIsSame('media/foo', ['image' => ['source' => 'foo']]);
        $this->assertSourceIsSame('media/foo', ['image' => ['source' => 'media/foo']]);
        $this->assertSourceIsSame('media/foo', ['image' => ['source' => '_media/foo']]);
    }

    public function testImagePathsAreNormalizedForCustomizedMediaDirectory()
    {
        Hyde::setMediaDirectory('_assets');

        $this->assertSourceIsSame('assets/foo', ['image' => 'foo']);
        $this->assertSourceIsSame('assets/foo', ['image' => 'assets/foo']);
        $this->assertSourceIsSame('assets/foo', ['image' => '_assets/foo']);

        $this->assertSourceIsSame('assets/foo', ['image' => ['source' => 'foo']]);
        $this->assertSourceIsSame('assets/foo', ['image' => ['source' => 'assets/foo']]);
        $this->assertSourceIsSame('assets/foo', ['image' => ['source' => '_assets/foo']]);
    }

    public function testImagePathsAreNormalizedForCustomizedMediaDirectoryWithoutUnderscore()
    {
        Hyde::setMediaDirectory('assets');

        $this->assertSourceIsSame('assets/foo', ['image' => 'foo']);
        $this->assertSourceIsSame('assets/foo', ['image' => 'assets/foo']);
        $this->assertSourceIsSame('assets/foo', ['image' => 'assets/foo']);

        $this->assertSourceIsSame('assets/foo', ['image' => ['source' => 'foo']]);
        $this->assertSourceIsSame('assets/foo', ['image' => ['source' => 'assets/foo']]);
        $this->assertSourceIsSame('assets/foo', ['image' => ['source' => 'assets/foo']]);
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
