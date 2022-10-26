<?php
declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Services\Markdown;

use Hyde\Framework\Contracts\MarkdownShortcodeContract;
use Hyde\Framework\Modules\Markdown\ShortcodeProcessor;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Modules\Markdown\ShortcodeProcessor
 */
class ShortcodeProcessorTest extends TestCase
{
    public function test_constructor_discovers_default_shortcodes()
    {
        $shortcodes = (new ShortcodeProcessor('foo'))->shortcodes;

        $this->assertCount(4, $shortcodes);
        $this->assertContainsOnlyInstancesOf(MarkdownShortcodeContract::class, $shortcodes);
    }

    public function test_discovered_shortcodes_are_used_to_process_input()
    {
        $processor = new \Hyde\Framework\Modules\Markdown\ShortcodeProcessor('>info foo');

        $this->assertEquals('<blockquote class="info">foo</blockquote>',
            $processor->run());
    }

    public function test_string_without_shortcode_is_not_modified()
    {
        $processor = new \Hyde\Framework\Modules\Markdown\ShortcodeProcessor('foo');

        $this->assertEquals('foo', $processor->run());
    }

    public function test_process_static_shorthand()
    {
        $this->assertEquals('<blockquote class="info">foo</blockquote>',
            ShortcodeProcessor::preprocess('>info foo'));
    }

    public function test_shortcodes_can_be_added_to_processor()
    {
        $processor = new \Hyde\Framework\Modules\Markdown\ShortcodeProcessor('foo');

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

        $this->assertArrayHasKey('foo', $processor->shortcodes);
        $this->assertEquals('bar', $processor->run());
    }
}
