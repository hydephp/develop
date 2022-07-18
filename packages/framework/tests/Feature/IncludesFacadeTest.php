<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Facades\Includes;
use Hyde\Framework\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Facades\Includes
 */
class IncludesFacadeTest extends TestCase
{
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

    public function test_markdown_returns_default_value_when_not_found()
    {
        $this->assertNull(Includes::markdown('foo.md'));
        $this->assertEquals('default', Includes::markdown('foo.md', 'default'));
    }

    public function test_blade_returns_rendered_partial()
    {
        $expected = 'foo bar';
        file_put_contents(Hyde::path('resources/_includes/foo.blade.php'), '{{ "foo bar" }}');
        $this->assertEquals($expected, Includes::blade('foo.blade.php'));
        unlink(Hyde::path('resources/_includes/foo.blade.php'));
    }

    public function test_blade_returns_default_value_when_not_found()
    {
        $this->assertNull(Includes::blade('foo.blade.php'));
        $this->assertEquals('default', Includes::blade('foo.blade.php', 'default'));
    }
}
