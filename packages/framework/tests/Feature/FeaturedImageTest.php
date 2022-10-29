<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Features\Blogging\Models\FeaturedImage;
use Hyde\Testing\TestCase;

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
}

class NullImage extends FeaturedImage
{
    public function __construct()
    {
        parent::__construct(null, null, null, null, null, null, null);
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
        parent::__construct('alt', 'title', 'author', 'authorUrl', 'copyright', 'license', 'licenseUrl');
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
