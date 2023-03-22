<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Actions;

use Hyde\Framework\Actions\GeneratesTableOfContents;
use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Framework\Actions\GeneratesTableOfContents
 */
class GeneratesSidebarTableOfContentsTest extends UnitTestCase
{
    public static function setUpBeforeClass(): void
    {
        self::mockConfig();
    }

    public function testCanGenerateTableOfContents()
    {
        $markdown = "# Level 1\n## Level 2\n## Level 2B\n### Level 3\n";
        $result = (new GeneratesTableOfContents($markdown))->execute();

        $this->assertIsString($result);
        $this->assertStringContainsString('<ul>', $result);
        $this->assertStringContainsString('<a href="#level-2">Level 2</a>', $result);
        $this->assertStringNotContainsString('[[END_TOC]]', $result);
    }

    public function testReturnStringContainsExpectedContent()
    {
        $markdown = <<<'MARKDOWN'
        # Level 1
        ## Level 2
        ### Level 3
        
        MARKDOWN;

        $result = (new GeneratesTableOfContents($markdown))->execute();

        $this->assertSame(<<<'HTML'
            <ul class="table-of-contents"><li><a href="#level-2">Level 2</a><ul><li><a href="#level-3">Level 3</a></li></ul></li></ul>
            HTML,
            str_replace("\n", '', $result)
        );
    }
}
