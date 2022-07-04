<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Testing\ResetsApplication;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Services\BuildService
 */
class BuildServiceTest extends TestCase
{
    use ResetsApplication;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetSite();
    }

    protected function tearDown(): void
    {
        $this->resetSite();
        parent::tearDown();
    }
}
