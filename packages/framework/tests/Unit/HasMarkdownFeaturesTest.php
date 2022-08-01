<?php

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Helpers\Markdown;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Config;

/**
 * @covers \Hyde\Framework\Helpers\Markdown
 */
class HasMarkdownFeaturesTest extends TestCase
{
    public function test_has_table_of_contents()
    {
        $this->assertIsBool(Markdown::hasTableOfContents());

        Config::set('docs.table_of_contents.enabled', true);
        $this->assertTrue(Markdown::hasTableOfContents());

        Config::set('docs.table_of_contents.enabled', false);
        $this->assertFalse(Markdown::hasTableOfContents());
    }
}
