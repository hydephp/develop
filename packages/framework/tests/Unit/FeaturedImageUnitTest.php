<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Exceptions\FileNotFoundException;
use Hyde\Framework\Features\Blogging\Models\FeaturedImage;
use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Framework\Features\Blogging\Models\FeaturedImage
 *
 * @see \Hyde\Framework\Testing\Feature\FeaturedImageTest
 */
class FeaturedImageUnitTest extends UnitTestCase
{
    public static function setUpBeforeClass(): void
    {
        self::needsKernel();
    }

    public function testCanConstruct()
    {
        $this->assertInstanceOf(FeaturedImage::class, new FeaturedImage('foo'));
    }

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

    public function testGetType()
    {
        $this->assertEquals('local', (new LocalImage)->getType());
        $this->assertEquals('remote', (new RemoteImage)->getType());
    }

    public function testGetContentLength()
    {
        $this->assertEquals(0, (new NullImage)->getContentLength());
        $this->assertEquals(0, (new FilledImage)->getContentLength());
    }

    public function testFeaturedImageGetContentLengthWithNoSource()
    {
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('Featured image [_media/foo] not found.');

        $image = new FeaturedImage('_media/foo', ...$this->defaultArguments());
        $this->assertEquals(0, $image->getContentLength());
    }

    public function testCanConstructFeaturedImageWithRemoteSource()
    {
        $image = new FeaturedImage('http/foo', ...$this->defaultArguments());
        $this->assertInstanceOf(FeaturedImage::class, $image);

        $this->assertEquals('http/foo', $image->getSource());
    }

    public function testCanConstructFeaturedImageWithHttps()
    {
        $image = new FeaturedImage('https/foo', ...$this->defaultArguments());
        $this->assertInstanceOf(FeaturedImage::class, $image);

        $this->assertEquals('https/foo', $image->getSource());
    }

    protected function defaultArguments(): array
    {
        return ['alt', 'title', 'author', 'authorUrl', 'copyright', 'license', 'licenseUrl'];
    }
}

class LocalImage extends FeaturedImage
{
    public function __construct()
    {
        parent::__construct('source');
    }
}

class RemoteImage extends FeaturedImage
{
    public function __construct()
    {
        parent::__construct('https://example.com');
    }
}

class NullImage extends FeaturedImage
{
    public function __construct()
    {
        parent::__construct('source');
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
        parent::__construct('source', 'alt', 'title', 'author', 'authorUrl', 'license', 'licenseUrl', 'copyright');
    }

    public function getContentLength(): int
    {
        return 0;
    }
}
