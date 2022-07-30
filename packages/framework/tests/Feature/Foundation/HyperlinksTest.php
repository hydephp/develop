<?php

namespace Hyde\Framework\Testing\Feature\Foundation;

use Hyde\Framework\Foundation\Hyperlinks;
use Hyde\Framework\HydeKernel;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Foundation\Hyperlinks
 */
class HyperlinksTest extends TestCase
{
    protected Hyperlinks $class;

    protected function setUp(): void
    {
        parent::setUp();

        $this->class = new Hyperlinks(HydeKernel::getInstance());
    }
}
