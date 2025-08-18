<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Hyde;
use Hyde\Testing\UnitTestCase;
use Illuminate\Support\Carbon;
use Hyde\Framework\Actions\CreatesNewMarkdownPostFile;

/**
 * @see \Hyde\Framework\Testing\Feature\Commands\MakePostCommandTest
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Framework\Actions\CreatesNewMarkdownPostFile::class)]
class CreatesNewMarkdownPostFileTest extends UnitTestCase
{
    protected static bool $needsKernel = true;
    protected static bool $needsConfig = true;

    protected function setUp(): void
    {
        Carbon::setTestNow(Carbon::create(2024));
    }

    public function testWithDefaultData()
    {
        $action = new CreatesNewMarkdownPostFile('Example Title', null, null, null);

        $this->assertSame([
            'title' => 'Example Title',
            'description' => 'A short description used in previews and SEO',
            'category' => 'blog',
            'author' => 'default',
            'date' => '2024-01-01 00:00',
        ], $action->toArray());
    }

    public function testWithCustomData()
    {
        $action = new CreatesNewMarkdownPostFile('foo', 'bar', 'baz', 'qux', '2024-06-01 12:20');

        $this->assertSame([
            'title' => 'foo',
            'description' => 'bar',
            'category' => 'baz',
            'author' => 'qux',
            'date' => '2024-06-01 12:20',
        ], $action->toArray());
    }

    public function testSave()
    {
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

    public function testSaveWithCustomContent()
    {
        $action = new CreatesNewMarkdownPostFile('Example Post', null, null, null, null, 'Hello World!');
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

        Hello World!

        MARKDOWN, file_get_contents($path));

        unlink($path);
    }

    public function testCustomDateNormalisation()
    {
        $action = new CreatesNewMarkdownPostFile('Example Post', null, null, null, 'Jan 1 2024 8am');

        $this->assertSame('2024-01-01 08:00', $action->toArray()['date']);
    }
}
