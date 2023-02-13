<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Facades\Config;
use Hyde\Testing\TestCase;

use function config;

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

    public function testGetArrayWithArray()
    {
        config(['foo' => ['bar' => 'baz']]);

        $this->assertSame(['bar' => 'baz'], Config::getArray('foo'));
    }

    public function testGetArrayWithNull()
    {
        config(['foo' => null]);

        $this->assertSame([], Config::getArray('foo'));
    }

    public function testGetArrayWithString()
    {
        config(['foo' => 'bar']);

        $this->assertSame(['bar'], Config::getArray('foo'));
    }

    public function testGetArrayWithBool()
    {
        config(['foo' => true]);

        $this->assertSame([true], Config::getArray('foo'));
    }

    public function testGetArrayWithInt()
    {
        config(['foo' => 1]);

        $this->assertSame([1], Config::getArray('foo'));
    }

    public function testGetArrayWithFloat()
    {
        config(['foo' => 1.1]);

        $this->assertSame([1.1], Config::getArray('foo'));
    }

    public function testGetStringWithArray()
    {
        config(['foo' => ['bar' => 'baz']]);

        $this->assertSame('Array', Config::getString('foo'));
    }

    public function testGetStringWithNull()
    {
        config(['foo' => null]);

        $this->assertSame('', Config::getString('foo'));
    }

    public function testGetStringWithString()
    {
        config(['foo' => 'bar']);

        $this->assertSame('bar', Config::getString('foo'));
    }

    public function testGetStringWithBool()
    {
        config(['foo' => true]);

        $this->assertSame('1', Config::getString('foo'));
    }

    public function testGetStringWithInt()
    {
        config(['foo' => 1]);

        $this->assertSame('1', Config::getString('foo'));
    }

    public function testGetStringWithFloat()
    {
        config(['foo' => 1.1]);

        $this->assertSame('1.1', Config::getString('foo'));
    }

    public function testGetBoolWithArray()
    {
        config(['foo' => ['bar' => 'baz']]);

        $this->assertTrue(Config::getBool('foo'));
    }

    public function testGetBoolWithNull()
    {
        config(['foo' => null]);

        $this->assertFalse(Config::getBool('foo'));
    }

    public function testGetBoolWithString()
    {
        config(['foo' => 'bar']);

        $this->assertTrue(Config::getBool('foo'));
    }

    public function testGetBoolWithBool()
    {
        config(['foo' => true]);

        $this->assertTrue(Config::getBool('foo'));
    }

    public function testGetBoolWithInt()
    {
        config(['foo' => 1]);

        $this->assertTrue(Config::getBool('foo'));
    }

    public function testGetBoolWithFloat()
    {
        config(['foo' => 1.1]);

        $this->assertTrue(Config::getBool('foo'));
    }

    public function testGetIntWithArray()
    {
        config(['foo' => ['bar' => 'baz']]);

        $this->assertSame(0, Config::getInt('foo'));
    }

    public function testGetIntWithNull()
    {
        config(['foo' => null]);

        $this->assertSame(0, Config::getInt('foo'));
    }

    public function testGetIntWithString()
    {
        config(['foo' => 'bar']);

        $this->assertSame(0, Config::getInt('foo'));
    }

    public function testGetIntWithBool()
    {
        config(['foo' => true]);

        $this->assertSame(1, Config::getInt('foo'));
    }

    public function testGetIntWithInt()
    {
        config(['foo' => 1]);

        $this->assertSame(1, Config::getInt('foo'));
    }

    public function testGetIntWithFloat()
    {
        config(['foo' => 1.1]);

        $this->assertSame(1, Config::getInt('foo'));
    }

    public function testGetFloatWithArray()
    {
        config(['foo' => ['bar' => 'baz']]);

        $this->assertSame(0.0, Config::getFloat('foo'));
    }

    public function testGetFloatWithNull()
    {
        config(['foo' => null]);

        $this->assertSame(0.0, Config::getFloat('foo'));
    }

    public function testGetFloatWithString()
    {
        config(['foo' => 'bar']);

        $this->assertSame(0.0, Config::getFloat('foo'));
    }

    public function testGetFloatWithBool()
    {
        config(['foo' => true]);

        $this->assertSame(1.0, Config::getFloat('foo'));
    }

    public function testGetFloatWithInt()
    {
        config(['foo' => 1]);

        $this->assertSame(1.0, Config::getFloat('foo'));
    }

    public function testGetFloatWithFloat()
    {
        config(['foo' => 1.1]);

        $this->assertSame(1.1, Config::getFloat('foo'));
    }
}
