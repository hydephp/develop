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

        $this->assertSameIgnoringIndentation(<<<'HTML'
            <ul class="table-of-contents">
                <li>
                    <a href="#level-2">Level 2</a>
                    <ul>
                        <li>
                            <a href="#level-3">Level 3</a>
                        </li>
                    </ul>
                </li>
            </ul>
            HTML, (new GeneratesTableOfContents($markdown))->execute()
        );
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

        $this->assertSameIgnoringIndentation(<<<'HTML'
            <ul class="table-of-contents">
                <li>
                    <a href="#level-2">Level 2</a>
                </li>
                <li>
                    <a href="#level-2b">Level 2B</a>
                </li>
            </ul>
            HTML, (new GeneratesTableOfContents($markdown))->execute()
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

        $this->assertSameIgnoringIndentation(<<<'HTML'
            <ul class="table-of-contents">
                <li>
                    <a href="#level-2">Level 2</a>
                    <ul>
                        <li>
                            <a href="#level-3">Level 3</a>
                        </li>
                    </ul>
                </li>
            </ul>
            HTML, (new GeneratesTableOfContents($markdown))->execute()
        );
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

        $this->assertSameIgnoringIndentation(<<<'HTML'
            <ul class="table-of-contents">
                <li>
                    <a href="#level-2">Level 2</a>
                    <ul>
                        <li>
                            <a href="#level-3">Level 3</a>
                            <ul>
                                <li>
                                    <a href="#level-4">Level 4</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#level-2b">Level 2B</a>
                    <ul>
                        <li>
                            <a href="#level-3b">Level 3B</a>
                        </li>
                        <li>
                            <a href="#level-3c">Level 3C</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#level-2c">Level 2C</a>
                    <ul>
                        <li>
                            <a href="#level-3d">Level 3D</a>
                        </li>
                    </ul>
                </li>
            </ul>
            HTML, (new GeneratesTableOfContents($markdown))->execute()
        );
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

        $this->assertSameIgnoringIndentation(<<<'HTML'
            <ul class="table-of-contents">
                <li>
                    <a href="#level-2">Level 2</a>
                    <ul>
                        <li>
                            <a href="#level-3">Level 3</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#level-2b">Level 2B</a>
                    <ul>
                        <li>
                            <a href="#level-3b">Level 3B</a>
                        </li>
                    </ul>
                </li>
            </ul>
            HTML, (new GeneratesTableOfContents($markdown))->execute()
        );
    }

    protected function assertSameIgnoringIndentation(string $expected, string $actual): void
    {
        $this->assertSame(
            $this->removeIndentation(trim($expected)),
            $this->removeIndentation(trim($actual))
        );
    }

    protected function removeIndentation(string $actual): string
    {
        return implode("\n", array_map('trim', explode("\n", $actual)));
    }
}
