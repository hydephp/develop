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
    public function test_path_returns_the_includes_directory()
    {
        $this->assertEquals(
            Hyde::path('resources/includes'),
            Includes::path()
        );
    }

    public function test_path_returns_a_partial_within_the_includes_directory()
    {
        $this->assertEquals(
            Hyde::path('resources/includes/partial.html'),
            Includes::path('partial.html')
        );
    }

    public function test_path_creates_directory_if_it_does_not_exist()
    {
        $path = Includes::path();
        File::deleteDirectory($path);
        $this->assertFalse(File::exists($path));
        $this->assertTrue(File::exists(Includes::path()));
    }

    public function test_get_returns_partial()
    {
        $expected = 'foo bar';
        file_put_contents(Hyde::path('resources/includes/foo.txt'), $expected);
        $this->assertEquals($expected, Includes::get('foo.txt'));
        Filesystem::unlink('resources/includes/foo.txt');
    }

    public function test_get_returns_default_value_when_not_found()
    {
        $this->assertNull(Includes::get('foo.txt'));
        $this->assertEquals('default', Includes::get('foo.txt', 'default'));
    }

    public function test_html_returns_rendered_partial()
    {
        $expected = '<h1>foo bar</h1>';
        file_put_contents(Hyde::path('resources/includes/foo.html'), '<h1>foo bar</h1>');
        $this->assertEquals($expected, Includes::html('foo.html'));
        Filesystem::unlink('resources/includes/foo.html');
    }

    public function test_html_returns_efault_value_when_not_found()
    {
        $this->assertNull(Includes::html('foo.html'));
        $this->assertEquals('<h1>default</h1>', Includes::html('foo.html', '<h1>default</h1>'));
    }

    public function test_html_with_and_without_extension()
    {
        file_put_contents(Hyde::path('resources/includes/foo.html'), '# foo bar');
        $this->assertEquals(Includes::html('foo.html'), Includes::html('foo'));
        Filesystem::unlink('resources/includes/foo.html');
    }

    public function test_markdown_returns_rendered_partial()
    {
        $expected = "<h1>foo bar</h1>\n";
        file_put_contents(Hyde::path('resources/includes/foo.md'), '# foo bar');
        $this->assertEquals($expected, Includes::markdown('foo.md'));
        Filesystem::unlink('resources/includes/foo.md');
    }

    public function test_markdown_returns_rendered_default_value_when_not_found()
    {
        $this->assertNull(Includes::markdown('foo.md'));
        $this->assertEquals("<h1>default</h1>\n", Includes::markdown('foo.md', '# default'));
    }

    public function test_markdown_with_and_without_extension()
    {
        file_put_contents(Hyde::path('resources/includes/foo.md'), '# foo bar');
        $this->assertEquals(Includes::markdown('foo.md'), Includes::markdown('foo'));
        Filesystem::unlink('resources/includes/foo.md');
    }

    public function test_blade_returns_rendered_partial()
    {
        $expected = 'foo bar';
        file_put_contents(Hyde::path('resources/includes/foo.blade.php'), '{{ "foo bar" }}');
        $this->assertEquals($expected, Includes::blade('foo.blade.php'));
        Filesystem::unlink('resources/includes/foo.blade.php');
    }

    public function test_blade_with_and_without_extension()
    {
        file_put_contents(Hyde::path('resources/includes/foo.blade.php'), '# foo bar');
        $this->assertEquals(Includes::blade('foo.blade.php'), Includes::blade('foo'));
        Filesystem::unlink('resources/includes/foo.blade.php');
    }

    public function test_blade_returns_rendered_default_value_when_not_found()
    {
        $this->assertNull(Includes::blade('foo.blade.php'));
        $this->assertEquals('default', Includes::blade('foo.blade.php', '{{ "default" }}'));
    }
}
