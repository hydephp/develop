<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Actions;

use Hyde\Framework\Actions\ConvertsMarkdownToPlainText;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Actions\ConvertsMarkdownToPlainText
 */
class ConvertsMarkdownToPlainTextTest extends TestCase
{
    public function testItRemovesHeadings()
    {
        $markdown = <<<'MD'
        # Heading level 1
        ## Heading level 2
        ### Heading level 3
        #### Heading level 4
        ##### Heading level 5
        ###### Heading level 6
        MD;

        $text = <<<'TXT'
        Heading level 1
        Heading level 2
        Heading level 3
        Heading level 4
        Heading level 5
        Heading level 6
        TXT;

        $this->assertSame($text, $this->convert($markdown));
    }

    public function testItRemovesHeadingsAlternateSyntax()
    {
        $markdown = <<<'MD'
        Heading level 1
        ================

        Heading level 2
        ---------------
        MD;

        $text = <<<'TXT'
        Heading level 1

        Heading level 2

        TXT;

        $this->assertSame($text, $this->convert($markdown));
    }

    public function testItRemovesParagraphs()
    {
        $markdown = <<<'MD'
        I really like using Markdown.

        I think I'll use it to format all of my documents from now on.
        MD;

        $text = <<<'TXT'
        I really like using Markdown.

        I think I'll use it to format all of my documents from now on.
        TXT;

        $this->assertSame($text, $this->convert($markdown));
    }

    public function testItRemovesParagraphsMultiline()
    {
        $markdown = <<<'MD'
        This is the first line.
        And this is the second line.
        MD;

        $text = <<<'TXT'
        This is the first line.
        And this is the second line.
        TXT;

        $this->assertSame($text, $this->convert($markdown));
    }

    public function testItRemovesLinebreaks()
    {
        $markdown = <<<'MD'
        First line with two spaces after.
        And the next line.

        First line with the HTML tag after.<br>
        And the next line.
        MD;

        $text = <<<'TXT'
        First line with two spaces after.
        And the next line.

        First line with the HTML tag after.
        And the next line.
        TXT;

        $this->assertSame($text, $this->convert($markdown));
    }

    public function testItRemovesBold()
    {
        $markdown = <<<'MD'
        I just love **bold text**.
        I just love __bold text__.
        Love**is**bold
        MD;

        $text = <<<'TXT'
        I just love bold text.
        I just love bold text.
        Loveisbold
        TXT;

        $this->assertSame($text, $this->convert($markdown));
    }

    public function testItRemovesItalic()
    {
        $markdown = <<<'MD'
        Italicized text is the *cat's meow*.
        Italicized text is the _cat's meow_.
        A*cat*meow
        MD;

        $text = <<<'TXT'
        Italicized text is the cat's meow.
        Italicized text is the cat's meow.
        Acatmeow
        TXT;

        $this->assertSame($text, $this->convert($markdown));
    }

    public function testItRemovesBoldAndItalic()
    {
        $markdown = <<<'MD'
        This text is ***really important***.
        This text is ___really important___.
        This text is __*really important*__.
        This text is **_really important_**.
        This is really***very***important text.
        MD;

        $text = <<<'TXT'
        This text is really important.
        This text is really important.
        This text is really important.
        This text is really important.
        This is reallyveryimportant text.
        TXT;

        $this->assertSame($text, $this->convert($markdown));
    }

    public function testItRemovesBlockquotes()
    {
        $markdown = <<<'MD'
        > Dorothy followed her through many of the beautiful rooms in her castle.
        MD;

        $text = <<<'TXT'
        Dorothy followed her through many of the beautiful rooms in her castle.
        TXT;

        $this->assertSame($text, $this->convert($markdown));
    }

    public function testItRemovesBlockquotesWithMultipleParagraphs()
    {
        $markdown = <<<'MD'
        > Dorothy followed her through many of the beautiful rooms in her castle.
        >
        > The Witch bade her clean the pots and kettles and sweep the floor and keep the fire fed with wood.
        MD;

        $text = <<<'TXT'
        Dorothy followed her through many of the beautiful rooms in her castle.

        The Witch bade her clean the pots and kettles and sweep the floor and keep the fire fed with wood.
        TXT;

        $this->assertSame($text, $this->convert($markdown));
    }

    public function testItRemovesOrderedLists()
    {
        $markdown = <<<'MD'
        1. First item
        2. Second item
        3. Third item
        4. Fourth item

        15. Fifth item
            1. Indented item
            2. Indented item
        16. Sixth item
        MD;

        $text = $markdown;

        $this->assertSame($text, $this->convert($markdown));
    }

    public function testItRemovesUnorderedLists()
    {
        $markdown = <<<'MD'
        - First item
        - Second item
        - Third item
        - Fourth item

        * First item
        * Second item
        * Third item
        * Fourth item

        + First item
        + Second item
        + Third item
        + Fourth item

        - First item
        - Second item
        - Third item
            - Indented item
            - Indented item
        - Fourth item
        MD;

        $text = $markdown;

        $this->assertSame($text, $this->convert($markdown));
    }

    public function testItRemovesCode()
    {
        $markdown = <<<'MD'
        At the command prompt, type `nano`.
        MD;

        $text = <<<'TXT'
        At the command prompt, type nano.
        TXT;

        $this->assertSame($text, $this->convert($markdown));
    }

    public function testItRemovesCodeBlocks()
    {
        $markdown = <<<'MD'
        <p>Hello World</p>
        MD;

        $text = <<<'TXT'
        Hello World
        TXT;

        $this->assertSame($text, $this->convert($markdown));
    }

    public function testItRemovesHorizontalRules()
    {
        $markdown = <<<'MD'
        ***

        ---

        _________________
        MD;

        $text = <<<'TXT'

        TXT;

        $this->assertSame($text, $this->convert($markdown));
    }

    public function testItRemovesLinks()
    {
        $markdown = <<<'MD'
        My favorite search engine is [Duck Duck Go](https://duckduckgo.com).

        My favorite search engine is [Duck Duck Go](https://duckduckgo.com "The best search engine for privacy").

        <https://www.markdownguide.org>
        <fake@example.com>

        I love supporting the **[EFF](https://eff.org)**.
        This is the *[Markdown Guide](https://www.markdownguide.org)*.
        See the section on [`code`](#code).

        [link](https://www.example.com/my%20great%20page)

        <a href="https://www.example.com/my great page">link</a>
        MD;

        $text = <<<'TXT'
        My favorite search engine is Duck Duck Go.

        My favorite search engine is Duck Duck Go.

        I love supporting the EFF.
        This is the Markdown Guide.
        See the section on code.

        link

        link
        TXT;

        $this->assertSame($text, $this->convert($markdown));
    }

    public function testItRemovesImages()
    {
        $markdown = <<<'MD'
        ![The San Juan Mountains are beautiful!](/assets/images/san-juan-mountains.jpg)
        ![The San Juan Mountains are beautiful!](/assets/images/san-juan-mountains.jpg "San Juan Mountains")
        MD;

        $text = <<<'TXT'
        The San Juan Mountains are beautiful!
        The San Juan Mountains are beautiful!
        TXT;

        $this->assertSame($text, $this->convert($markdown));
    }

    public function testItRemovesLinkingImages()
    {
        $markdown = <<<'MD'
        [![An old rock in the desert](/assets/images/shiprock.jpg "Shiprock, New Mexico by Beau Rogers")](https://www.flickr.com/photos/example.png)
        MD;

        $text = <<<'TXT'
        An old rock in the desert
        TXT;

        $this->assertSame($text, $this->convert($markdown));
    }

    public function testItRemovesHtml()
    {
        $markdown = <<<'MD'
        This **word** is bold. This <em>word</em> is italic.

        <footer class="footer">
            <div>
                <p>&copy; My Company</p>
            </div>
        </footer>

        <title>Hello World</title>
        MD;

        $text = <<<'TXT'
        This word is bold. This word is italic.

        
        &copy; My Company
        

        Hello World
        TXT;

        $this->assertSame($text, $this->convert($markdown));
    }

    public function testItRemovesFootnotes()
    {
        $markdown = <<<'MD'
        Here's a sentence with a footnote.[^1]

        [^1]: This is the footnote.
        MD;

        $text = <<<'TXT'
        Here's a sentence with a footnote.


        TXT;

        $this->assertSame($text, $this->convert($markdown));
    }

    public function testItRemovesFootnotesAlternate()
    {
        $markdown = <<<'MD'
        Here's a sentence with a footnote.[^note]

        [^note]: This is the footnote.
        MD;

        $text = <<<'TXT'
        Here's a sentence with a footnote.


        TXT;

        $this->assertSame($text, $this->convert($markdown));
    }

    public function testItReplacesConsecutivelyOccurringNewlines()
    {
        $markdown = <<<'MD'
        Start


        Break




        End
        MD;

        $text = <<<'TXT'
        Start

        Break

        End
        TXT;

        $this->assertSame($text, $this->convert($markdown));
    }

    public function testItRemovesFencedCodeBlocks()
    {
        $markdown = <<<'MD'
        ```php
        echo 'Hello World';
        ```
        MD;

        $text = <<<'TXT'
        echo 'Hello World';

        TXT;

        $this->assertSame($text, $this->convert($markdown));
    }

    public function testItRemovesFencedCodeBlocksAlternate()
    {
        $markdown = <<<'MD'
        ~~~php
        echo 'Hello World';
        ~~~
        MD;

        $text = <<<'TXT'
        echo 'Hello World';

        TXT;

        $this->assertSame($text, $this->convert($markdown));
    }

    public function testItRemovesTables()
    {
        $markdown = <<<'MD'
        | Syntax    | Description |
        |-----------|-------------|
        | Header    | Title       |
        | Paragraph | Text        |
        MD;

        $text = <<<'TXT'
        Syntax    Description

        Header    Title
        Paragraph Text
        TXT;

        $this->assertSame($text, $this->convert($markdown));
    }

    public function testItTrimsIndentation()
    {
        $markdown = <<<'MD'
        foo
            bar
                baz
        MD;

        $text = <<<'TXT'
        foo
        bar
        baz
        TXT;

        $this->assertSame($text, $this->convert($markdown));
    }

    public function testWithEmptyString()
    {
        $this->assertSame('', $this->convert(''));
    }

    public function testWithOnlyEmptyLines()
    {
        $this->assertSame("\n", $this->convert("\n\n\n"));
    }

    protected function convert(string $markdown): string
    {
        return (new ConvertsMarkdownToPlainText($markdown))->execute();
    }
}
