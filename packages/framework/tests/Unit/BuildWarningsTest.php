<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Support\BuildWarnings;
use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Support\BuildWarnings
 */
class BuildWarningsTest extends UnitTestCase
{
    protected function tearDown(): void
    {
        BuildWarnings::getInstance()->clear();

        parent::tearDown();
    }

    public function testGetInstance()
    {
        //
    }

    public function testHasWarnings()
    {
        //
    }

    public function testGetWarnings()
    {
        //
    }

    public function testReport()
    {
        //
    }

    public function testReportsWarnings()
    {
        //
    }

    public function testWriteWarningsToOutput()
    {
        //
    }

    public function testAdd()
    {
        //
    }

    public function testGet()
    {
        //
    }

    public function testClear()
    {
        //
    }
}
