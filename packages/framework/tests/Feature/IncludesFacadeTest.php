<?php
declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Helpers\Includes;
use Hyde\Framework\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;

/**
 * @covers \Hyde\Framework\Helpers\Includes
 */
class IncludesFacadeTest extends TestCase
{
    public function test_path_returns_the_includes_directory()
    {
        $this->assertEquals(
            Hyde::path('resources/_includes'),
            Includes::path()
        );
    }

    public function test_path_returns_a_partial_within_the_includes_directory()
    {
        $this->assertEquals(
            Hyde::path('resources/_includes/partial.html'),
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
        file_put_contents(Hyde::path('resources/_includes/foo.txt'), $expected);
        $this->assertEquals($expected, Includes::get('foo.txt'));
        unlink(Hyde::path('resources/_includes/foo.txt'));
    }

    public function test_get_returns_default_value_when_not_found()
    {
        $this->assertNull(Includes::get('foo.txt'));
        $this->assertEquals('default', Includes::get('foo.txt', 'default'));
    }

    public function test_markdown_returns_rendered_partial()
    {
        $expected = "<h1>foo bar</h1>\n";
        file_put_contents(Hyde::path('resources/_includes/foo.md'), '# foo bar');
        $this->assertEquals($expected, Includes::markdown('foo.md'));
        unlink(Hyde::path('resources/_includes/foo.md'));
    }

    public function test_markdown_returns_rendered_default_value_when_not_found()
    {
        $this->assertNull(Includes::markdown('foo.md'));
        $this->assertEquals("<h1>default</h1>\n", Includes::markdown('foo.md', '# default'));
    }

    public function test_blade_returns_rendered_partial()
    {
        $expected = 'foo bar';
        file_put_contents(Hyde::path('resources/_includes/foo.blade.php'), '{{ "foo bar" }}');
        $this->assertEquals($expected, Includes::blade('foo.blade.php'));
        unlink(Hyde::path('resources/_includes/foo.blade.php'));
    }

    public function test_blade_returns_rendered_default_value_when_not_found()
    {
        $this->assertNull(Includes::blade('foo.blade.php'));
        $this->assertEquals('default', Includes::blade('foo.blade.php', '{{ "default" }}'));
    }
}
