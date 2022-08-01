<?php

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Helpers\Markdown;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Config;

/**
 * @covers \Hyde\Framework\Helpers\Markdown
 */
class HasMarkdownFeaturesTest extends TestCase
{
    public function test_has_table_of_contents()
    {
        $this->assertIsBool(DocumentationPage::hasTableOfContents());

        Config::set('docs.table_of_contents.enabled', true);
        $this->assertTrue(DocumentationPage::hasTableOfContents());

        Config::set('docs.table_of_contents.enabled', false);
        $this->assertFalse(DocumentationPage::hasTableOfContents());
    }
}
