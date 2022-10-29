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
}
