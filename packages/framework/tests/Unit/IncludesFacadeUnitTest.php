<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Mockery;
use Hyde\Hyde;
use Hyde\Support\Includes;
use Hyde\Testing\UnitTestCase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Filesystem\Filesystem;

/**
 * @covers \Hyde\Support\Includes
 *
 * @see \Hyde\Framework\Testing\Feature\IncludesFacadeTest
 */
class IncludesFacadeUnitTest extends UnitTestCase
{
    protected static bool $needsKernel = true;

    protected function setUp(): void
    {
        parent::setUp();

        Blade::swap(Mockery::mock());
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function testPathReturnsTheIncludesDirectory()
    {
        $this->assertSame(Hyde::path('resources/includes'), Includes::path());
    }

    public function testPathReturnsAPartialWithinTheIncludesDirectory()
    {
        $this->assertSame(Hyde::path('resources/includes/partial.html'), Includes::path('partial.html'));
    }

    public function testGetReturnsPartial()
    {
        $filename = 'foo.txt';
        $expected = 'foo bar';

        $filesystem = Mockery::mock(Filesystem::class);

        $filesystem->shouldReceive('exists')->with(Hyde::path('resources/includes/'.$filename))->andReturn(true);
        $filesystem->shouldReceive('get')->with(Hyde::path('resources/includes/'.$filename))->andReturn($expected);

        app()->instance(Filesystem::class, $filesystem);

        $this->assertSame($expected, Includes::get($filename));
    }

    public function testGetReturnsDefaultValueWhenNotFound()
    {
        $filename = 'foo.txt';
        $default = 'default';

        $filesystem = Mockery::mock(Filesystem::class);
        $filesystem->shouldReceive('exists')->with(Hyde::path('resources/includes/'.$filename))->andReturn(false);

        app()->instance(Filesystem::class, $filesystem);

        $this->assertNull(Includes::get($filename));
        $this->assertSame($default, Includes::get($filename, $default));
    }

    public function testHtmlReturnsRenderedPartial()
    {
        $filename = 'foo.html';
        $expected = '<h1>foo bar</h1>';

        $filesystem = Mockery::mock(Filesystem::class);
        $filesystem->shouldReceive('exists')->with(Hyde::path('resources/includes/'.$filename))->andReturn(true);
        $filesystem->shouldReceive('get')->with(Hyde::path('resources/includes/'.$filename))->andReturn($expected);

        app()->instance(Filesystem::class, $filesystem);

        $this->assertSame($expected, Includes::html($filename));
    }

    public function testHtmlReturnsDefaultValueWhenNotFound()
    {
        $filename = 'foo.html';
        $default = '<h1>default</h1>';

        $filesystem = Mockery::mock(Filesystem::class);
        $filesystem->shouldReceive('exists')->with(Hyde::path('resources/includes/'.$filename))->andReturn(false);

        app()->instance(Filesystem::class, $filesystem);

        $this->assertNull(Includes::html($filename));
        $this->assertSame($default, Includes::html($filename, $default));
    }

    public function testHtmlWithAndWithoutExtension()
    {
        $filename = 'foo.html';
        $content = '<h1>foo bar</h1>';

        $filesystem = Mockery::mock(Filesystem::class);
        $filesystem->shouldReceive('exists')->with(Hyde::path('resources/includes/'.$filename))->andReturn(true);
        $filesystem->shouldReceive('get')->with(Hyde::path('resources/includes/'.$filename))->andReturn($content);

        app()->instance(Filesystem::class, $filesystem);

        $this->assertSame(Includes::html('foo.html'), Includes::html('foo'));
    }

    public function testMarkdownReturnsRenderedPartial()
    {
        $filename = 'foo.md';
        $content = '# foo bar';
        $expected = "<h1>foo bar</h1>\n";

        $filesystem = Mockery::mock(Filesystem::class);
        $filesystem->shouldReceive('exists')->with(Hyde::path('resources/includes/'.$filename))->andReturn(true);
        $filesystem->shouldReceive('get')->with(Hyde::path('resources/includes/'.$filename))->andReturn($content);

        app()->instance(Filesystem::class, $filesystem);

        $this->assertSame($expected, Includes::markdown($filename));
    }

    public function testMarkdownReturnsRenderedDefaultValueWhenNotFound()
    {
        $filename = 'foo.md';
        $default = '# default';
        $expected = "<h1>default</h1>\n";

        $filesystem = Mockery::mock(Filesystem::class);
        $filesystem->shouldReceive('exists')->with(Hyde::path('resources/includes/'.$filename))->andReturn(false);

        app()->instance(Filesystem::class, $filesystem);

        $this->assertNull(Includes::markdown($filename));
        $this->assertSame($expected, Includes::markdown($filename, $default));
    }

    public function testMarkdownWithAndWithoutExtension()
    {
        $filename = 'foo.md';
        $content = '# foo bar';
        $expected = "<h1>foo bar</h1>\n";

        $filesystem = Mockery::mock(Filesystem::class);
        $filesystem->shouldReceive('exists')->with(Hyde::path('resources/includes/'.$filename))->andReturn(true);
        $filesystem->shouldReceive('get')->with(Hyde::path('resources/includes/'.$filename))->andReturn($content);

        app()->instance(Filesystem::class, $filesystem);

        $this->assertSame($expected, Includes::markdown('foo.md'));
        $this->assertSame(Includes::markdown('foo.md'), Includes::markdown('foo'));
        $this->assertSame(Includes::markdown('foo.md'), Includes::markdown('foo.md'));
    }

    public function testBladeReturnsRenderedPartial()
    {
        $filename = 'foo.blade.php';
        $content = '{{ "foo bar" }}';
        $expected = 'foo bar';

        $filesystem = Mockery::mock(Filesystem::class);
        $filesystem->shouldReceive('exists')->with(Hyde::path('resources/includes/'.$filename))->andReturn(true);
        $filesystem->shouldReceive('get')->with(Hyde::path('resources/includes/'.$filename))->andReturn($content);

        app()->instance(Filesystem::class, $filesystem);

        Blade::shouldReceive('render')->with($content)->andReturn($expected);

        $this->assertSame($expected, Includes::blade($filename));
    }

    public function testBladeWithAndWithoutExtension()
    {
        $filename = 'foo.blade.php';
        $content = '{{ "foo bar" }}';
        $expected = 'foo bar';

        $filesystem = Mockery::mock(Filesystem::class);
        $filesystem->shouldReceive('exists')->with(Hyde::path('resources/includes/'.$filename))->andReturn(true);
        $filesystem->shouldReceive('get')->with(Hyde::path('resources/includes/'.$filename))->andReturn($content);

        app()->instance(Filesystem::class, $filesystem);

        Blade::shouldReceive('render')->with($content)->andReturn($expected);

        $this->assertSame(Includes::blade('foo.blade.php'), Includes::blade('foo'));
    }

    public function testBladeReturnsDefaultValueWhenNotFound()
    {
        $filename = 'foo.blade.php';
        $default = '{{ "default" }}';
        $expected = 'default';

        $filesystem = Mockery::mock(Filesystem::class);
        $filesystem->shouldReceive('exists')->with(Hyde::path('resources/includes/'.$filename))->andReturn(false);

        app()->instance(Filesystem::class, $filesystem);

        Blade::shouldReceive('render')->with($default)->andReturn($expected);

        $this->assertNull(Includes::blade($filename));
        $this->assertSame($expected, Includes::blade($filename, $default));
    }
}
