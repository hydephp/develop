<?php

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Concerns\HasFeaturedImage;
use Hyde\Framework\Models\FrontMatter;
use Hyde\Framework\Models\Image;
use Hyde\Testing\TestCase;

/**
 * Class HasFeaturedImageTest.
 *
 * @covers \Hyde\Framework\Concerns\HasFeaturedImage
 */
class HasFeaturedImageTest extends TestCase
{
    use HasFeaturedImage;

    protected FrontMatter $matter;

    protected function matter(...$args)
    {
        return $this->matter->get(...$args);
    }

}
