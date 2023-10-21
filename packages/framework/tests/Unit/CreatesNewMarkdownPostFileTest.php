<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Testing\TestCase;
use Illuminate\Support\Str;
use Hyde\Framework\Actions\CreatesNewMarkdownPostFile;

/**
 * @covers \Hyde\Framework\Actions\CreatesNewMarkdownPostFile
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\MakePostCommandTest
 */
class CreatesNewMarkdownPostFileTest extends TestCase
{
    public function testWithDefaultData()
    {
        $action = new CreatesNewMarkdownPostFile('Example Title', null, null, null);
        $array = $action->toArray();

        // Truncate date as it can cause tests to fail when the clock switches over a second
        $array['date'] = Str::before($array['date'], ' ');

        $this->assertSame([
            'title' => 'Example Title',
            'description' => 'A short description used in previews and SEO',
            'category' => 'blog',
            'author' => 'default',
            'date' => date('Y-m-d'),
        ], $array);
    }

    public function testWithCustomData()
    {
        $action = new CreatesNewMarkdownPostFile('foo', 'bar', 'baz', 'qux');
        $array = $action->toArray();

        // Truncate date as it can cause tests to fail when the clock switches over a second
        $array['date'] = Str::before($array['date'], ' ');

        $this->assertSame([
            'title' => 'foo',
            'description' => 'bar',
            'category' => 'baz',
            'author' => 'qux',
            'date' => date('Y-m-d'),
        ], $array);
    }
}
