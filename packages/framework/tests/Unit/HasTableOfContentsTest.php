<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Pages\DocumentationPage;
use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Pages\DocumentationPage
 *
 * @see \Hyde\Framework\Testing\Unit\GeneratesSidebarTableOfContentsTest
 */
class HasTableOfContentsTest extends UnitTestCase
{
    protected static bool $needsKernel = true;
    protected static bool $needsConfig = true;

    public function testConstructorCreatesTableOfContentsString()
    {
        $this->assertSame(
            '<ul class="table-of-contents"><li><a href="#title">Title</a></li></ul>',
            str_replace("\n", '', (new DocumentationPage(markdown: '## Title'))->getTableOfContents())
        );
    }
}
