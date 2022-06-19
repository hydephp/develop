<?php

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Concerns\Markdown\HasMarkdownFeatures;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Config;

/**
 * @covers \Hyde\Framework\Concerns\Markdown\HasMarkdownFeatures
 */
class HasMarkdownFeaturesTest extends TestCase
{
    use HasMarkdownFeatures;

    public function test_has_table_of_contents()
    {
        $this->assertIsBool(static::hasTableOfContents());

        Config::set('docs.table_of_contents.enabled', true);
        $this->assertTrue(static::hasTableOfContents());

        Config::set('docs.table_of_contents.enabled', false);
        $this->assertFalse(static::hasTableOfContents());
    }
}
