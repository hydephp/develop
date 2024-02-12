<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Services\Markdown;

use Hyde\Markdown\Contracts\MarkdownShortcodeContract;
use Hyde\Markdown\Processing\ShortcodeProcessor;
use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Markdown\Processing\ShortcodeProcessor
 */
class ShortcodeProcessorTest extends UnitTestCase
{
    public function testConstructorDiscoversDefaultShortcodes()
    {
        $shortcodes = (new ShortcodeProcessor('foo'))->getShortcodes();

        $this->assertCount(4, $shortcodes);
        $this->assertContainsOnlyInstancesOf(MarkdownShortcodeContract::class, $shortcodes);
    }

    public function testDiscoveredShortcodesAreUsedToProcessInput()
    {
        $processor = new ShortcodeProcessor('>info foo');

        $this->assertEquals('<blockquote class="info"><p>foo</p></blockquote>',
            $processor->run());
    }

    public function testStringWithoutShortcodeIsNotModified()
    {
        $processor = new ShortcodeProcessor('foo');

        $this->assertEquals('foo', $processor->run());
    }

    public function testProcessStaticShorthand()
    {
        $this->assertEquals('<blockquote class="info"><p>foo</p></blockquote>',
            ShortcodeProcessor::preprocess('>info foo'));
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
        $this->assertEquals('bar', $processor->run());
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
        $this->assertEquals('bar', $processor->run());
    }
}
