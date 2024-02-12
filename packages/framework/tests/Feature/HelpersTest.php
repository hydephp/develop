<?php

/** @noinspection PhpFullyQualifiedNameUsageInspection */

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Foundation\HydeKernel;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * Covers the helpers in helpers.php.
 *
 * @see \Hyde\Framework\Testing\Unit\UnixsumTest for additional tests of the unixsum function
 */
class HelpersTest extends TestCase
{
    /** @covers ::hyde */
    public function testHydeFunctionExists()
    {
        $this->assertTrue(function_exists('hyde'));
    }

    /** @covers ::hyde */
    public function testHydeFunctionReturnsHydeKernelClass()
    {
        $this->assertInstanceOf(HydeKernel::class, hyde());
    }

    /** @covers ::hyde */
    public function testCanCallMethodsOnReturnedHydeClass()
    {
        $this->assertSame(Hyde::path(), hyde()->path());
    }

    /** @covers ::unslash */
    public function testUnslashFunctionExists()
    {
        $this->assertTrue(function_exists('unslash'));
    }

    /** @covers ::unslash */
    public function testUnslashFunctionTrimsTrailingSlashes()
    {
        $tests = ['foo',  '/foo',  'foo/',  '/foo/',  '\foo\\',  '\\/foo/\\'];

        foreach ($tests as $test) {
            $this->assertSame('foo', unslash($test));
        }

        $tests = ['',  '/',  '\\',  '/\\'];

        foreach ($tests as $test) {
            $this->assertSame('', unslash($test));
        }

        $tests = ['foo/bar',  'foo/bar/',  'foo/bar\\',  '\\/foo/bar/\\'];

        foreach ($tests as $test) {
            $this->assertSame('foo/bar', unslash($test));
        }
    }

    /** @covers ::\Hyde\hyde */
    public function testHydeFunctionExistsInHydeNamespace()
    {
        $this->assertTrue(function_exists('Hyde\hyde'));
    }

    /** @covers ::\Hyde\hyde */
    public function testNamespacedHydeFunction()
    {
        $this->assertSame(hyde(), \Hyde\hyde());
    }

    /** @covers ::\Hyde\unslash */
    public function testUnslashFunctionExistsInHydeNamespace()
    {
        $this->assertTrue(function_exists('Hyde\unslash'));
    }

    /** @covers ::\Hyde\unslash */
    public function testNamespacedUnslashFunction()
    {
        $this->assertSame(unslash('foo'), \Hyde\unslash('foo'));
    }

    /** @covers ::\Hyde\unixsum */
    public function testUnixsumFunction()
    {
        $this->assertSame(md5("foo\n"), \Hyde\unixsum("foo\n"));
        $this->assertSame(md5("foo\n"), \Hyde\unixsum("foo\r\n"));
    }

    /** @covers ::\Hyde\unixsum_file */
    public function testUnixsumFileFunction()
    {
        $this->file('unix.txt', "foo\n");
        $this->file('windows.txt', "foo\r\n");

        $this->assertSame(md5("foo\n"), \Hyde\unixsum_file('unix.txt'));
        $this->assertSame(md5("foo\n"), \Hyde\unixsum_file('windows.txt'));
    }

    /** @covers ::\Hyde\make_title */
    public function testHydeMakeTitleFunction()
    {
        $this->assertSame(Hyde::makeTitle('foo'), \Hyde\make_title('foo'));
    }

    /** @covers ::\Hyde\normalize_newlines */
    public function testHydeNormalizeNewlinesFunction()
    {
        $this->assertSame(Hyde::normalizeNewlines('foo'), \Hyde\normalize_newlines('foo'));
    }

    /** @covers ::\Hyde\strip_newlines */
    public function testHydeStripNewlinesFunction()
    {
        $this->assertSame(Hyde::stripNewlines('foo'), \Hyde\strip_newlines('foo'));
    }

    /** @covers ::\Hyde\trim_slashes */
    public function testHydeTrimSlashesFunction()
    {
        $this->assertSame(Hyde::trimSlashes('foo'), \Hyde\trim_slashes('foo'));
    }

    /** @covers ::\Hyde\evaluate_arrayable */
    public function testHydeEvaluateArrayableFunction()
    {
        $this->assertSame(['foo'], \Hyde\evaluate_arrayable(['foo']));
        $this->assertSame(['foo'], \Hyde\evaluate_arrayable(collect(['foo'])));
    }

    /** @covers ::\Hyde\yaml_encode */
    public function testHydeYamlEncodeFunction()
    {
        $this->assertSame("foo: bar\n", \Hyde\yaml_encode(['foo' => 'bar']));
    }

    /** @covers ::\Hyde\yaml_encode */
    public function testHydeYamlEncodeFunctionEncodesArrayables()
    {
        $this->assertSame("foo: bar\n", \Hyde\yaml_encode(collect(['foo' => 'bar'])));
    }

    /** @covers ::\Hyde\yaml_encode */
    public function testHydeYamlEncodeFunctionAcceptsParameters()
    {
        $this->assertSame(
            Yaml::dump(['foo' => 'bar'], 4, 2, 128),
            \Hyde\yaml_encode(['foo' => 'bar'], 4, 2, 128)
        );
    }

    /** @covers ::\Hyde\yaml_decode */
    public function testHydeYamlDecodeFunction()
    {
        $this->assertSame(['foo' => 'bar'], \Hyde\yaml_decode("foo: bar\n"));
    }

    /** @covers ::\Hyde\yaml_decode */
    public function testHydeYamlDecodeFunctionAcceptsParameters()
    {
        $this->assertSame(
            Yaml::parse('foo: bar', 128),
            \Hyde\yaml_decode('foo: bar', 128)
        );
    }

    /** @covers ::\Hyde\path_join */
    public function testHydePathJoinFunction()
    {
        $this->assertSame('foo/bar', \Hyde\path_join('foo', 'bar'));
    }

    /** @covers ::\Hyde\path_join */
    public function testHydePathJoinFunctionWithMultiplePaths()
    {
        $this->assertSame('foo/bar/baz', \Hyde\path_join('foo', 'bar', 'baz'));
    }

    /** @covers ::\Hyde\normalize_slashes */
    public function testHydeNormalizeSlashesFunction()
    {
        $this->assertSame('foo/bar', \Hyde\normalize_slashes('foo\\bar'));
        $this->assertSame('foo/bar', \Hyde\normalize_slashes('foo/bar'));
    }
}
