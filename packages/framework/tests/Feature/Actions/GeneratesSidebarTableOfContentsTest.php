<?php

/** @noinspection HtmlUnknownAnchorTarget */

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Actions;

use Hyde\Framework\Actions\GeneratesTableOfContents;
use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Framework\Actions\GeneratesTableOfContents
 */
class GeneratesSidebarTableOfContentsTest extends UnitTestCase
{
    protected static bool $needsConfig = true;

    public function testCanGenerateTableOfContents()
    {
        $markdown = "# Level 1\n## Level 2\n## Level 2B\n### Level 3\n";
        $result = (new GeneratesTableOfContents($markdown))->execute();
    }

    public function testReturnStringContainsExpectedContent()
    {
        $markdown = <<<'MARKDOWN'
        # Level 1
        ## Level 2
        ### Level 3
        MARKDOWN;
    }

    public function testCanGenerateTableOfContentsForDocumentUsingSetextHeaders()
    {
        $markdown = <<<'MARKDOWN'
        Level 1
        =======
        Level 2
        -------
        Level 2B
        --------
        MARKDOWN;

        $expected = <<<'MARKDOWN'
        # Level 1
        ## Level 2
        ## Level 2B
        MARKDOWN;

        $this->assertSame(
            (new GeneratesTableOfContents($expected))->execute(),
            (new GeneratesTableOfContents($markdown))->execute()
        );
    }

    public function testNonHeadingMarkdownIsRemoved()
    {
        $expected = <<<'MARKDOWN'
        # Level 1
        ## Level 2
        ### Level 3
        MARKDOWN;

        $actual = <<<'MARKDOWN'
        # Level 1
        Foo bar
        ## Level 2
        Bar baz
        ### Level 3
        Baz foo
        MARKDOWN;

        $this->assertSame(
            (new GeneratesTableOfContents($expected))->execute(),
            (new GeneratesTableOfContents($actual))->execute()
        );
    }

    public function testWithNoLevelOneHeading()
    {
        $markdown = <<<'MARKDOWN'
        ## Level 2
        ### Level 3
        MARKDOWN;
    }

    public function testWithMultipleNestedHeadings()
    {
        $markdown = <<<'MARKDOWN'
        # Level 1
        ## Level 2
        ### Level 3
        #### Level 4
        ##### Level 5
        ###### Level 6

        ## Level 2B
        ### Level 3B
        ### Level 3C
        ## Level 2C
        ### Level 3D
        MARKDOWN;
    }

    public function testWithMultipleLevelOneHeadings()
    {
        $markdown = <<<'MARKDOWN'
        # Level 1
        ## Level 2
        ### Level 3
        # Level 1B
        ## Level 2B
        ### Level 3B
        MARKDOWN;
    }

    public function testWithNoHeadings()
    {
        $this->assertSame([], (new GeneratesTableOfContents("Foo bar\nBaz foo"))->execute());
    }

    public function testWithNoContent()
    {
        $this->assertSame([], (new GeneratesTableOfContents(''))->execute());
    }
}
