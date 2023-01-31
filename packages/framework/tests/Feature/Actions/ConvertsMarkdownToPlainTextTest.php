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
        This text is *really important*.
        This text is _really important_.
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
        
        5. First item
        6. Second item
        7. Third item
        8. Fourth item
        
        9. First item
        10. Second item
        11. Third item
        12. Fourth item
        
        13. First item
        14. Second item
        15. Third item
            1. Indented item
            2. Indented item
        16. Fourth item
        MD;

        $text = <<<'TXT'
        1. First item
        2. Second item
        3. Third item
        4. Fourth item
        
        5. First item
        6. Second item
        7. Third item
        8. Fourth item
        
        9. First item
        10. Second item
        11. Third item
        12. Fourth item
        
        13. First item
        14. Second item
        15. Third item
            1. Indented item
            2. Indented item
        16. Fourth item
        TXT;

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

                $text = <<<'TXT'
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
        TXT;

        $this->assertSame($text, $this->convert($markdown));
    }

    protected function convert(string $markdown): string
    {
        return (new ConvertsMarkdownToPlainText($markdown))->execute();
    }
}
