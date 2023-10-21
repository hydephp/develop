<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Testing\TestCase;
use Illuminate\Support\Carbon;
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
        Carbon::setTestNow(Carbon::create(2024));

        $action = new CreatesNewMarkdownPostFile('Example Title', null, null, null);
        $array = $action->toArray();

        $this->assertSame([
            'title' => 'Example Title',
            'description' => 'A short description used in previews and SEO',
            'category' => 'blog',
            'author' => 'default',
            'date' => '2024-01-01 00:00',
        ], $array);
    }

    public function testWithCustomData()
    {
        Carbon::setTestNow(Carbon::create(2024));

        $action = new CreatesNewMarkdownPostFile('foo', 'bar', 'baz', 'qux');
        $array = $action->toArray();

        $this->assertSame([
            'title' => 'foo',
            'description' => 'bar',
            'category' => 'baz',
            'author' => 'qux',
            'date' => '2024-01-01 00:00',
        ], $array);
    }
}
