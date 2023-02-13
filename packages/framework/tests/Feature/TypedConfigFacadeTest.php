<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Facades\Config;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Facades\Config
 */
class TypedConfigFacadeTest extends TestCase
{
    public function testGetArray()
    {
        $this->assertIsArray(Config::getArray('foo'));
    }

    public function testGetString()
    {
        $this->assertIsString(Config::getString('foo'));
    }

    public function testGetBool()
    {
        $this->assertIsBool(Config::getBool('foo'));
    }

    public function testGetInt()
    {
        $this->assertIsInt(Config::getInt('foo'));
    }

    public function testGetFloat()
    {
        $this->assertIsFloat(Config::getFloat('foo'));
    }
}
