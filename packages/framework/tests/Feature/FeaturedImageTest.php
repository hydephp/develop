<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Exceptions\FileNotFoundException;
use Hyde\Framework\Features\Blogging\Models\FeaturedImage;
use Hyde\Framework\Features\Blogging\Models\LocalFeaturedImage;
use Hyde\Framework\Features\Blogging\Models\RemoteFeaturedImage;
use Hyde\Testing\TestCase;
use InvalidArgumentException;

/**
 * @covers \Hyde\Framework\Features\Blogging\Models\FeaturedImage
 */
class FeaturedImageTest extends TestCase
{
    public function testGetAltText()
    {
        $this->assertNull((new NullImage)->getAltText());
        $this->assertEquals('alt', (new FilledImage)->getAltText());
    }

    public function testGetTitleText()
    {
        $this->assertNull((new NullImage)->getTitleText());
        $this->assertEquals('title', (new FilledImage)->getTitleText());
    }

    public function testGetAuthorName()
    {
        $this->assertNull((new NullImage)->getAuthorName());
        $this->assertEquals('author', (new FilledImage)->getAuthorName());
    }

    public function testGetAuthorUrl()
    {
        $this->assertNull((new NullImage)->getAuthorUrl());
        $this->assertEquals('authorUrl', (new FilledImage)->getAuthorUrl());
    }

    public function testGetCopyrightText()
    {
        $this->assertNull((new NullImage)->getCopyrightText());
        $this->assertEquals('copyright', (new FilledImage)->getCopyrightText());
    }

    public function testGetLicenseName()
    {
        $this->assertNull((new NullImage)->getLicenseName());
        $this->assertEquals('license', (new FilledImage)->getLicenseName());
    }

    public function testGetLicenseUrl()
    {
        $this->assertNull((new NullImage)->getLicenseUrl());
        $this->assertEquals('licenseUrl', (new FilledImage)->getLicenseUrl());
    }

    public function testHasAltText()
    {
        $this->assertFalse((new NullImage)->hasAltText());
        $this->assertTrue((new FilledImage)->hasAltText());
    }

    public function testHasTitleText()
    {
        $this->assertFalse((new NullImage)->hasTitleText());
        $this->assertTrue((new FilledImage)->hasTitleText());
    }

    public function testHasAuthorName()
    {
        $this->assertFalse((new NullImage)->hasAuthorName());
        $this->assertTrue((new FilledImage)->hasAuthorName());
    }

    public function testHasAuthorUrl()
    {
        $this->assertFalse((new NullImage)->hasAuthorUrl());
        $this->assertTrue((new FilledImage)->hasAuthorUrl());
    }

    public function testHasCopyrightText()
    {
        $this->assertFalse((new NullImage)->hasCopyrightText());
        $this->assertTrue((new FilledImage)->hasCopyrightText());
    }

    public function testHasLicenseName()
    {
        $this->assertFalse((new NullImage)->hasLicenseName());
        $this->assertTrue((new FilledImage)->hasLicenseName());
    }

    public function testHasLicenseUrl()
    {
        $this->assertFalse((new NullImage)->hasLicenseUrl());
        $this->assertTrue((new FilledImage)->hasLicenseUrl());
    }

    public function testGetMetadataArray()
    {
        $this->assertSame([
            'url' => 'source',
            'contentUrl' => 'source',
        ], (new NullImage)->getMetadataArray());

        $this->assertSame([
            'text' => 'alt',
            'name' => 'title',
            'url' => 'source',
            'contentUrl' => 'source',
        ], (new FilledImage)->getMetadataArray());
    }

    public function testGetContentLength()
    {
        $this->assertEquals(0, (new NullImage)->getContentLength());
        $this->assertEquals(0, (new FilledImage)->getContentLength());
    }

    public function testCanConstructLocalFeaturedImage()
    {
        $image = new LocalFeaturedImage('_media/foo', ...$this->defaultArguments());
        $this->assertInstanceOf(LocalFeaturedImage::class, $image);
        $this->assertInstanceOf(FeaturedImage::class, $image);

        $this->assertEquals('_media/foo', $image->getSource());
    }

    public function testCannotConstructLocalFeaturedImageWithInvalidSource()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('LocalFeaturedImage source must start with _media/');

        new LocalFeaturedImage('foo', ...$this->defaultArguments());
    }

    public function testFeaturedImageGetContentLength()
    {
        $this->file('_media/foo', 'image');

        $image = new LocalFeaturedImage('_media/foo', ...$this->defaultArguments());
        $this->assertEquals(5, $image->getContentLength());
    }

    public function testFeaturedImageGetContentLengthWithNoSource()
    {
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('Image at _media/foo does not exist');

        $image = new LocalFeaturedImage('_media/foo', ...$this->defaultArguments());
        $this->assertEquals(0, $image->getContentLength());
    }

    public function testCanConstructRemoteFeaturedImage()
    {
        $image = new RemoteFeaturedImage('http/foo', ...$this->defaultArguments());
        $this->assertInstanceOf(RemoteFeaturedImage::class, $image);
        $this->assertInstanceOf(FeaturedImage::class, $image);

        $this->assertEquals('http/foo', $image->getSource());
    }

    public function testCannotConstructRemoteFeaturedImageWithInvalidSource()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('RemoteFeaturedImage source must be a valid url');

        new RemoteFeaturedImage('foo', ...$this->defaultArguments());
    }

    protected function defaultArguments(): array
    {
        return ['alt', 'title', 'author', 'authorUrl', 'copyright', 'license', 'licenseUrl'];
    }
}

class NullImage extends FeaturedImage
{
    public function __construct()
    {
        parent::__construct('source',null, null, null, null, null, null, null);
    }

    public function getSource(): string
    {
        return 'source';
    }

    public function getContentLength(): int
    {
        return 0;
    }
}

class FilledImage extends FeaturedImage
{
    public function __construct()
    {
        parent::__construct('source', 'alt', 'title', 'author', 'authorUrl', 'copyright', 'license', 'licenseUrl');
    }

    public function getSource(): string
    {
        return 'source';
    }

    public function getContentLength(): int
    {
        return 0;
    }
}
