<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Services\Markdown;

use Hyde\Support\Facades\Render;
use Hyde\Support\Models\RenderData;
use Hyde\Markdown\Contracts\MarkdownShortcodeContract;
use Hyde\Markdown\Processing\ShortcodeProcessor;
use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Markdown\Processing\ShortcodeProcessor
 */
class ShortcodeProcessorTest extends UnitTestCase
{
    protected static bool $needsKernel = true;
    protected static bool $needsConfig = true;

    protected function setUp(): void
    {
        parent::setUp();

        // Todo: Fix coupling so we don't need to use the Render facade at all here.
        Render::swap(new RenderData());
    }

    public function testConstructorDiscoversDefaultShortcodes()
    {
        $shortcodes = (new ShortcodeProcessor('foo'))->getShortcodes();

        $this->assertCount(4, $shortcodes);
        $this->assertContainsOnlyInstancesOf(MarkdownShortcodeContract::class, $shortcodes);
    }

    public function testDiscoveredShortcodesAreUsedToProcessInput()
    {
        $processor = new ShortcodeProcessor('>info foo');

        $this->assertSame('<blockquote class="info"><p>foo</p></blockquote>', $processor->run());
    }

    public function testStringWithoutShortcodeIsNotModified()
    {
        $processor = new ShortcodeProcessor('foo');

        $this->assertSame('foo', $processor->run());
    }

    public function testProcessStaticShorthand()
    {
        $this->assertSame(
            '<blockquote class="info"><p>foo</p></blockquote>',
            ShortcodeProcessor::preprocess('>info foo')
        );
    }

    public function testShortcodesCanBeAddedToProcessor()
    {
        $processor = new ShortcodeProcessor('foo');

        $processor->addShortcode(new class implements MarkdownShortcodeContract
        {
            public static function signature(): string
            {
                return 'foo';
            }

            public static function resolve(string $input): string
            {
                return 'bar';
            }
        });

        $this->assertArrayHasKey('foo', $processor->getShortcodes());
        $this->assertSame('bar', $processor->run());
    }

    public function testShortcodesCanBeAddedToProcessorUsingArray()
    {
        $processor = new ShortcodeProcessor('foo');

        $processor->addShortcodesFromArray([new class implements MarkdownShortcodeContract
        {
            public static function signature(): string
            {
                return 'foo';
            }

            public static function resolve(string $input): string
            {
                return 'bar';
            }
        }]);

        $this->assertArrayHasKey('foo', $processor->getShortcodes());
        $this->assertSame('bar', $processor->run());
    }
}