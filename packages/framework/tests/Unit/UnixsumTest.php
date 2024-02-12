<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Foundation\HydeKernel;
use Illuminate\Filesystem\Filesystem;
use Mockery;
use PHPUnit\Framework\TestCase;

use function Hyde\unixsum;
use function Hyde\unixsum_file;

class UnixsumTest extends TestCase
{
    public function testMethodReturnsString()
    {
        $this->assertIsString(unixsum('foo'));
    }

    public function testMethodReturnsStringWithLengthOf32()
    {
        $this->assertEquals(32, strlen(unixsum('foo')));
    }

    public function testMethodReturnsStringMatchingExpectedFormat()
    {
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', unixsum('foo'));
    }

    public function testMethodReturnsSameValueForSameStringUsingNormalMethod()
    {
        $this->assertEquals(md5('foo'), unixsum('foo'));
    }

    public function testMethodReturnsDifferentValueForDifferentString()
    {
        $this->assertNotEquals(unixsum('foo'), unixsum('bar'));
    }

    public function testFunctionIsCaseSensitive()
    {
        $this->assertNotEquals(unixsum('foo'), unixsum('FOO'));
    }

    public function testFunctionIsSpaceSensitive()
    {
        $this->assertNotEquals(unixsum(' foo '), unixsum('foo'));
    }

    public function testMethodReturnsSameValueRegardlessOfEndOfLineSequence()
    {
        $this->assertEquals(unixsum('foo'), unixsum('foo'));
        $this->assertEquals(unixsum("foo\n"), unixsum("foo\n"));
        $this->assertEquals(unixsum("foo\n"), unixsum("foo\r"));
        $this->assertEquals(unixsum("foo\n"), unixsum("foo\r\n"));
    }

    public function testMethodReturnsSameValueForStringWithMixedEndOfLineSequences()
    {
        $this->assertEquals(unixsum("foo\nbar\r\nbaz\r\n"),
            unixsum("foo\nbar\nbaz\n"));
    }

    public function testMethodReturnsSameValueWhenLoadedFromFileUsingShorthand()
    {
        $string = "foo\nbar\r\nbaz\r\n";

        HydeKernel::setInstance(new HydeKernel());
        $filesystem = Mockery::mock(Filesystem::class);
        $filesystem->shouldReceive('get')->andReturn($string);
        app()->instance(Filesystem::class, $filesystem);

        $this->assertEquals(unixsum($string), unixsum_file('foo'));
    }
}
