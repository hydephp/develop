<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Factories\FeaturedImageFactory;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Testing\TestCase;

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
        
    }

    public function testMakeMethodCreatesRemoteImageWhenUrlIsSet()
    {
        
    }

    public function testMakeMethodCreatesRemoteImageWhenBothUrlAndPathIsSet()
    {

    }

    public function testMakeMethodThrowsExceptionIfNoPathInformationIsSet()
    {
        
    }

    public function testMakeMethodCanCreateImageFromJustString()
    {
        
    }
}
