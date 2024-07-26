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
    // Todo: Check the version is correct? (When running in monorepo)

    public function testVersionReturnsString()
    {
        $this->assertIsString(HydeFront::version());
    }

    public function testCdnLinkReturnsCorrectUrl()
    {
        $expected = 'https://cdn.jsdelivr.net/npm/hydefront@v3.4/dist/styles.css';
        $this->assertSame($expected, HydeFront::cdnLink('styles.css'));
    }

    public function testCdnLinkReturnsCorrectUrlForHydeCss()
    {
        $expected = 'https://cdn.jsdelivr.net/npm/hydefront@v3.4/dist/hyde.css';
        $this->assertSame($expected, HydeFront::cdnLink('hyde.css'));
    }

    public function testCdnLinkReturnsCorrectUrlForInvalidFile()
    {
        $expected = 'https://cdn.jsdelivr.net/npm/hydefront@v3.4/dist/invalid';
        $this->assertSame($expected, HydeFront::cdnLink('invalid'));
    }
}
