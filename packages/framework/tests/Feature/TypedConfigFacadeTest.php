<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Facades\Config;
use Hyde\Testing\TestCase;
use TypeError;
use stdClass;

/**
 * @covers \Hyde\Facades\Config
 */
class TypedConfigFacadeTest extends TestCase
{
    public function testGetArray()
    {
        config(['foo' => ['bar']]);
        $this->assertIsArray(Config::getArray('foo'));
    }

    public function testGetString()
    {
        config(['foo' => 'bar']);
        $this->assertIsString(Config::getString('foo'));
    }

    public function testGetBool()
    {
        config(['foo' => true]);
        $this->assertIsBool(Config::getBool('foo'));
    }

    public function testGetInt()
    {
        config(['foo' => 10]);
        $this->assertIsInt(Config::getInt('foo'));
    }

    public function testGetFloat()
    {
        config(['foo' => 10.0]);
        $this->assertIsFloat(Config::getFloat('foo'));
    }

    public function testGetArrayWithDefaultValue()
    {
        $this->assertSame(['bar'], Config::getArray('foo', ['bar']));
    }

    public function testGetStringWithDefaultValue()
    {
        $this->assertSame('bar', Config::getString('foo', 'bar'));
    }

    public function testGetBoolWithDefaultValue()
    {
        $this->assertSame(true, Config::getBool('foo', true));
    }

    public function testGetIntWithDefaultValue()
    {
        $this->assertSame(10, Config::getInt('foo', 10));
    }

    public function testGetFloatWithDefaultValue()
    {
        $this->assertSame(10.0, Config::getFloat('foo', 10.0));
    }

    public function testGetArrayWithStrictMode()
    {
        $this->runUnitTest(['bar'], ['bar'], Config::getArray(...));
    }

    public function testGetStringWithStrictMode()
    {
        $this->runUnitTest('bar', 'bar', Config::getString(...));
    }

    public function testGetBoolWithStrictMode()
    {
        $this->runUnitTest(true, true, Config::getBool(...));
    }

    public function testGetIntWithStrictMode()
    {
        $this->runUnitTest(10, 10, Config::getInt(...));
    }

    public function testGetFloatWithStrictMode()
    {
        $this->runUnitTest(10.0, 10.0, Config::getFloat(...));
    }

    public function testGetArrayWithFailingStrictMode()
    {
        $this->expectException(TypeError::class);
        $this->runUnitTest(null, null, Config::getArray(...));
    }

    public function testGetStringWithFailingStrictMode()
    {
        $this->expectException(TypeError::class);
        $this->runUnitTest(null, null, Config::getString(...));
    }

    public function testGetBoolWithFailingStrictMode()
    {
        $this->expectException(TypeError::class);
        $this->runUnitTest(null, null, Config::getBool(...));
    }

    public function testGetIntWithFailingStrictMode()
    {
        $this->expectException(TypeError::class);
        $this->runUnitTest(null, null, Config::getInt(...));
    }

    public function testGetFloatWithFailingStrictMode()
    {
        $this->expectException(TypeError::class);
        $this->runUnitTest(null, null, Config::getFloat(...));
    }

    public function testGetArrayWithArray()
    {
        $this->runUnitTest(['bar' => 'baz'], ['bar' => 'baz'], Config::getArray(...));
    }

    public function testGetStringWithString()
    {
        $this->runUnitTest('bar', 'bar', Config::getString(...));
    }

    public function testGetBoolWithBool()
    {
        $this->runUnitTest(true, true, Config::getBool(...));
    }

    public function testGetIntWithInt()
    {
        $this->runUnitTest(1, 1, Config::getInt(...));
    }

    public function testGetFloatWithFloat()
    {
        $this->runUnitTest(1.1, 1.1, Config::getFloat(...));
    }

    public function testGetNullableString()
    {
        config(['foo' => 'bar']);
        $this->assertIsString(Config::getNullableString('foo'));

        config(['foo' => null]);
        $this->assertNull(Config::getNullableString('foo'));
    }

    public function testInvalidTypeMessage()
    {
        config(['foo' => new stdClass()]);
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Hyde\Facades\Config::validated(): Config value foo must be of type array, object given');
        Config::getArray('foo');
    }

    protected function runUnitTest($actual, $expected, $method): void
    {
        config(['foo' => $actual]);
        $this->assertSame($expected, $method('foo'));
    }
}
