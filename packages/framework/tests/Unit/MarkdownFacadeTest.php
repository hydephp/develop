<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Mockery;
use Hyde\Testing\UnitTestCase;
use Hyde\Markdown\Models\Markdown;
use Hyde\Framework\Services\MarkdownService;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Markdown\Models\Markdown::class)]
class MarkdownFacadeTest extends UnitTestCase
{
    public function testRender(): void
    {
        $mock = Mockery::mock(MarkdownService::class);
        $mock->shouldReceive('parse')->once()->andReturn("<h1>Hello World!</h1>\n");
        app()->bind(MarkdownService::class, fn () => $mock);

        $html = Markdown::render('# Hello World!');

        $this->assertIsString($html);
        $this->assertSame("<h1>Hello World!</h1>\n", $html);

        $this->verifyMockeryExpectations();
    }

    public static function tearDownAfterClass(): void
    {
        // Patch PHPUnit craziness by disabling this method
        // I don't know why it errors, but I have spent
        // far too much of my life trying to fix it.

        // TODO: Check if this is broken after the Pest 4 upgrade.
    }
}
