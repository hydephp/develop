<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Testing\UnitTestCase;
use Illuminate\Filesystem\Filesystem;
use Mockery;

use function Hyde\unixsum;
use function Hyde\unixsum_file;

class UnixsumTest extends UnitTestCase
{
    public function testMethodReturnsString()
    {
        $this->assertIsString(unixsum('foo'));
    }

    public function testMethodReturnsStringWithLengthOf32()
    {
        $this->assertSame(32, strlen(unixsum('foo')));
    }

    public function testMethodReturnsStringMatchingExpectedFormat()
    {
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', unixsum('foo'));
    }

    public function testMethodReturnsSameValueForSameStringUsingNormalMethod()
    {
        $this->assertSame(md5('foo'), unixsum('foo'));
    }

    public function testMethodReturnsDifferentValueForDifferentString()
    {
        $this->assertNotSame(unixsum('foo'), unixsum('bar'));
    }

    public function testFunctionIsCaseSensitive()
    {
        $this->assertNotSame(unixsum('foo'), unixsum('FOO'));
    }

    public function testFunctionIsSpaceSensitive()
    {
        $this->assertNotSame(unixsum(' foo '), unixsum('foo'));
    }

    public function testMethodReturnsSameValueRegardlessOfEndOfLineSequence()
    {
        $this->assertSame(unixsum('foo'), unixsum('foo'));
        $this->assertSame(unixsum("foo\n"), unixsum("foo\n"));
        $this->assertSame(unixsum("foo\n"), unixsum("foo\r"));
        $this->assertSame(unixsum("foo\n"), unixsum("foo\r\n"));
    }

    public function testMethodReturnsSameValueForStringWithMixedEndOfLineSequences()
    {
        $this->assertSame(unixsum("foo\nbar\r\nbaz\r\n"), unixsum("foo\nbar\nbaz\n"));
    }

    public function testMethodReturnsSameValueWhenLoadedFromFileUsingShorthand()
    {
        self::resetKernel();

        $string = "foo\nbar\r\nbaz\r\n";

        app()->instance(Filesystem::class, Mockery::mock(Filesystem::class, [
            'get' => $string,
        ]));

        $this->assertSame(unixsum($string), unixsum_file('foo'));
    }
}
