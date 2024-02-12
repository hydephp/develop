<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Facades\Filesystem;
use Hyde\Support\Includes;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;

/**
 * @covers \Hyde\Support\Includes
 */
class IncludesFacadeTest extends TestCase
{
    public function testPathReturnsTheIncludesDirectory()
    {
        $this->assertEquals(
            Hyde::path('resources/includes'),
            Includes::path()
        );
    }

    public function testPathReturnsAPartialWithinTheIncludesDirectory()
    {
        $this->assertEquals(
            Hyde::path('resources/includes/partial.html'),
            Includes::path('partial.html')
        );
    }

    public function testPathCreatesDirectoryIfItDoesNotExist()
    {
        $path = Includes::path();
        File::deleteDirectory($path);
        $this->assertFalse(File::exists($path));
        $this->assertTrue(File::exists(Includes::path()));
    }

    public function testGetReturnsPartial()
    {
        $expected = 'foo bar';
        file_put_contents(Hyde::path('resources/includes/foo.txt'), $expected);
        $this->assertEquals($expected, Includes::get('foo.txt'));
        Filesystem::unlink('resources/includes/foo.txt');
    }

    public function testGetReturnsDefaultValueWhenNotFound()
    {
        $this->assertNull(Includes::get('foo.txt'));
        $this->assertEquals('default', Includes::get('foo.txt', 'default'));
    }

    public function testHtmlReturnsRenderedPartial()
    {
        $expected = '<h1>foo bar</h1>';
        file_put_contents(Hyde::path('resources/includes/foo.html'), '<h1>foo bar</h1>');
        $this->assertEquals($expected, Includes::html('foo.html'));
        Filesystem::unlink('resources/includes/foo.html');
    }

    public function testHtmlReturnsEfaultValueWhenNotFound()
    {
        $this->assertNull(Includes::html('foo.html'));
        $this->assertEquals('<h1>default</h1>', Includes::html('foo.html', '<h1>default</h1>'));
    }

    public function testHtmlWithAndWithoutExtension()
    {
        file_put_contents(Hyde::path('resources/includes/foo.html'), '# foo bar');
        $this->assertEquals(Includes::html('foo.html'), Includes::html('foo'));
        Filesystem::unlink('resources/includes/foo.html');
    }

    public function testMarkdownReturnsRenderedPartial()
    {
        $expected = "<h1>foo bar</h1>\n";
        file_put_contents(Hyde::path('resources/includes/foo.md'), '# foo bar');
        $this->assertEquals($expected, Includes::markdown('foo.md'));
        Filesystem::unlink('resources/includes/foo.md');
    }

    public function testMarkdownReturnsRenderedDefaultValueWhenNotFound()
    {
        $this->assertNull(Includes::markdown('foo.md'));
        $this->assertEquals("<h1>default</h1>\n", Includes::markdown('foo.md', '# default'));
    }

    public function testMarkdownWithAndWithoutExtension()
    {
        file_put_contents(Hyde::path('resources/includes/foo.md'), '# foo bar');
        $this->assertEquals(Includes::markdown('foo.md'), Includes::markdown('foo'));
        Filesystem::unlink('resources/includes/foo.md');
    }

    public function testBladeReturnsRenderedPartial()
    {
        $expected = 'foo bar';
        file_put_contents(Hyde::path('resources/includes/foo.blade.php'), '{{ "foo bar" }}');
        $this->assertEquals($expected, Includes::blade('foo.blade.php'));
        Filesystem::unlink('resources/includes/foo.blade.php');
    }

    public function testBladeWithAndWithoutExtension()
    {
        file_put_contents(Hyde::path('resources/includes/foo.blade.php'), '# foo bar');
        $this->assertEquals(Includes::blade('foo.blade.php'), Includes::blade('foo'));
        Filesystem::unlink('resources/includes/foo.blade.php');
    }

    public function testBladeReturnsRenderedDefaultValueWhenNotFound()
    {
        $this->assertNull(Includes::blade('foo.blade.php'));
        $this->assertEquals('default', Includes::blade('foo.blade.php', '{{ "default" }}'));
    }
}
