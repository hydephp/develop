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
            'path' => 'path',
            'url' => 'url',
            'description' => 'description',
            'title' => 'title',
            'copyright' => 'copyright',
            'license' => 'license',
            'licenseUrl' => 'licenseUrl',
            'author' => 'author',
            'attributionUrl' => 'attributionUrl',
        ];

        $expected = [
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
}
