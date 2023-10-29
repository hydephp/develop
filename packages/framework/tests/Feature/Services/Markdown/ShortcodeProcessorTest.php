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
    public function test_constructor_discovers_default_shortcodes()
    {
        $shortcodes = (new ShortcodeProcessor('foo'))->getShortcodes();

        $this->assertCount(4, $shortcodes);
        $this->assertContainsOnlyInstancesOf(MarkdownShortcodeContract::class, $shortcodes);
    }

    public function test_discovered_shortcodes_are_used_to_process_input()
    {
        $processor = new ShortcodeProcessor('>info foo');

        $this->assertEquals('<blockquote class="info"><p>foo</p></blockquote>',
            $processor->run());
    }

    public function test_string_without_shortcode_is_not_modified()
    {
        $processor = new ShortcodeProcessor('foo');

        $this->assertEquals('foo', $processor->run());
    }

    public function test_process_static_shorthand()
    {
        $this->assertEquals('<blockquote class="info"><p>foo</p></blockquote>',
            ShortcodeProcessor::preprocess('>info foo'));
    }

    public function test_shortcodes_can_be_added_to_processor()
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

    public function test_shortcodes_can_be_added_to_processor_using_array()
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
