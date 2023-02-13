<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use ErrorException;
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
        $this->runUnitTest(['bar' => 'baz'], ['bar' => 'baz'], Config::getArray(...));
    }

    public function testGetArrayWithNull()
    {
        $this->runUnitTest(null, [], Config::getArray(...));
    }

    public function testGetArrayWithString()
    {
        $this->runUnitTest('bar', ['bar'], Config::getArray(...));
    }

    public function testGetArrayWithBool()
    {
        $this->runUnitTest(true, [true], Config::getArray(...));
    }

    public function testGetArrayWithInt()
    {
        $this->runUnitTest(1, [1], Config::getArray(...));
    }

    public function testGetArrayWithFloat()
    {
        $this->runUnitTest(1.1, [1.1], Config::getArray(...));
    }

    public function testGetStringWithArray()
    {
        $this->expectException(ErrorException::class);
        $this->runUnitTest(['bar' => 'baz'], 'Array', Config::getString(...));
    }

    public function testGetStringWithNull()
    {
        $this->runUnitTest(null, '', Config::getString(...));
    }

    public function testGetStringWithString()
    {
        $this->runUnitTest('bar', 'bar', Config::getString(...));
    }

    public function testGetStringWithBool()
    {
        $this->runUnitTest(true, '1', Config::getString(...));
    }

    public function testGetStringWithInt()
    {
        $this->runUnitTest(1, '1', Config::getString(...));
    }

    public function testGetStringWithFloat()
    {
        $this->runUnitTest(1.1, '1.1', Config::getString(...));
    }

    public function testGetBoolWithArray()
    {
        $this->runUnitTest(['bar' => 'baz'], true, Config::getBool(...));
    }

    public function testGetBoolWithNull()
    {
        $this->runUnitTest(null, false, Config::getBool(...));
    }

    public function testGetBoolWithString()
    {
        $this->runUnitTest('bar', true, Config::getBool(...));
    }

    public function testGetBoolWithBool()
    {
        $this->runUnitTest(true, true, Config::getBool(...));
    }

    public function testGetBoolWithInt()
    {
        $this->runUnitTest(1, true, Config::getBool(...));
    }

    public function testGetBoolWithFloat()
    {
        $this->runUnitTest(1.1, true, Config::getBool(...));
    }

    public function testGetIntWithArray()
    {
        $this->runUnitTest(0, 0, Config::getInt(...));
    }

    public function testGetIntWithNull()
    {
        $this->runUnitTest(null, 0, Config::getInt(...));
    }

    public function testGetIntWithString()
    {
        $this->runUnitTest('bar', 0, Config::getInt(...));
    }

    public function testGetIntWithBool()
    {
        $this->runUnitTest(true, 1, Config::getInt(...));
    }

    public function testGetIntWithInt()
    {
        $this->runUnitTest(1, 1, Config::getInt(...));
    }

    public function testGetIntWithFloat()
    {
        $this->runUnitTest(1.1, 1, Config::getInt(...));
    }

    public function testGetFloatWithArray()
    {
        $this->runUnitTest(['bar' => 'baz'], 1.0, Config::getFloat(...));
    }

    public function testGetFloatWithNull()
    {
        $this->runUnitTest(null, 0.0, Config::getFloat(...));
    }

    public function testGetFloatWithString()
    {
        $this->runUnitTest('bar', 0.0, Config::getFloat(...));
    }

    public function testGetFloatWithBool()
    {
        $this->runUnitTest(true, 1.0, Config::getFloat(...));
    }

    public function testGetFloatWithInt()
    {
        $this->runUnitTest(1, 1.0, Config::getFloat(...));
    }

    public function testGetFloatWithFloat()
    {
        $this->runUnitTest(1.1, 1.1, Config::getFloat(...));
    }

    protected function runUnitTest($actual, $expected, $method): void
    {
        config(['foo' => $actual]);
        $this->assertSame($expected, $method('foo'));
    }
}
