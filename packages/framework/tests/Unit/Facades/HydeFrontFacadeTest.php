<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Facades;

use Hyde\Facades\HydeFront;
use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Facades\HydeFront
 */
class HydeFrontFacadeTest extends UnitTestCase
{
    public function testVersionReturnsString()
    {
        $this->assertIsString(HydeFront::version());
    }

    public function testCdnLinkReturnsCorrectUrl()
    {
        $expected = 'https://cdn.jsdelivr.net/npm/hydefront@v3.4/dist/styles.css';
        $this->assertSame($expected, HydeFront::cdnLink('styles.css'));
    }
}
