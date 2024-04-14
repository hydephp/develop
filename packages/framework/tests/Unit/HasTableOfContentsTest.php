<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Pages\DocumentationPage;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Pages\DocumentationPage
 *
 * @see \Hyde\Framework\Testing\Feature\Actions\GeneratesSidebarTableOfContentsTest
 */
class HasTableOfContentsTest extends TestCase
{
    public function testConstructorCreatesTableOfContentsString()
    {
        $page = new DocumentationPage(markdown: '## Title');

        $this->assertSame('<ul class="table-of-contents"><li><a href="#title">Title</a></li></ul>', str_replace("\n", '', $page->getTableOfContents()));
    }
}
