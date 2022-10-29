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
    //
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
