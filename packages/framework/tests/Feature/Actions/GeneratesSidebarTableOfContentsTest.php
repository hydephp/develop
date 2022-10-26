<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Actions;

use Hyde\Framework\Actions\GeneratesSidebarTableOfContents;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Actions\GeneratesSidebarTableOfContents
 */
class GeneratesSidebarTableOfContentsTest extends TestCase
{
    public function testCanGenerateTableOfContents()
    {
        $markdown = "# Level 1\n## Level 2\n## Level 2B\n### Level 3\n";
        $result = (new GeneratesSidebarTableOfContents($markdown))->execute();

        $this->assertIsString($result);
        $this->assertStringContainsString('<ul>', $result);
        $this->assertStringContainsString('<a href="#level-2">Level 2</a>', $result);
        $this->assertStringNotContainsString('[[END_TOC]]', $result);
    }

    public function testReturnStringContainsExpectedContent()
    {
        $markdown = "# Level 1\n## Level 2\n### Level 3\n";
        $result = (new GeneratesSidebarTableOfContents($markdown))->execute();

        $this->assertEquals('<ul class="table-of-contents"><li><a href="#level-2">Level 2</a><ul><li><a href="#level-3">Level 3</a></li></ul></li></ul>',
            str_replace("\n", '', $result)
        );
    }
}
