<?php

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Actions\ConvertsFooterMarkdown;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Actions\ConvertsFooterMarkdown
 */
class ConvertsFooterMarkdownTest extends TestCase
{
    public function test_execute_method_renders_config_defined_markdown_to_html()
    {
        config(['hyde.footer.markdown' => '# Foo bar']);

        $this->assertEquals(
            "<h1>Foo bar</h1>\n",
            ConvertsFooterMarkdown::execute()
        );
    }
}