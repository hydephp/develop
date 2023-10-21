<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Hyde;
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

    public function testSave()
    {
        Carbon::setTestNow(Carbon::create(2024));

        $action = new CreatesNewMarkdownPostFile('Example Post', null, null, null);
        $action->save();

        $path = Hyde::path('_posts/example-post.md');

        $this->assertSame(<<<'MARKDOWN'
        ---
        title: 'Example Post'
        description: 'A short description used in previews and SEO'
        category: blog
        author: default
        date: '2024-01-01 00:00'
        ---

        ## Write something awesome.
        
        MARKDOWN, file_get_contents($path));

        unlink($path);
    }
}
