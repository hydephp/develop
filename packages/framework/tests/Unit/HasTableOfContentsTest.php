<?php

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Models\Markdown;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Testing\TestCase;

/**
 * Class HasTableOfContentsTest.
 *
 * @covers \Hyde\Framework\Models\Pages\DocumentationPage
 *
 * @see \Hyde\Framework\Testing\Feature\Actions\GeneratesSidebarTableOfContentsTest
 */
class HasTableOfContentsTest extends TestCase
{
    public function testConstructorCreatesTableOfContentsString()
    {
        $page = new DocumentationPage();

        $page->markdown = new Markdown('## Title');
        $this->assertEquals('<ul class="table-of-contents"><li><a href="#title">Title</a></li></ul>', str_replace("\n", '', $page->getTableOfContents()));
    }
}
