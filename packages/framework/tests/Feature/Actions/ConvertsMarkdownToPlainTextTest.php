<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Actions;

use Hyde\Framework\Actions\ConvertsMarkdownToPlainText;
use Hyde\Testing\TestCase;
use Illuminate\Support\Str;

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
        MD;

        $text = <<<'TXT'
        This word is bold. This word is italic.
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

    public function test_benchmark()
    {
        \Illuminate\Support\Benchmark::dd(function () {
            $markdown = <<<'MD'
            # Markdown
            
            ## Headers
            
            # This is an H1
            ## This is an H2
            ### This is an H3
            #### This is an H4
            ##### This is an H5
            ###### This is an H6
            
            ## Emphasis
            
            *This text will be italic*
            _This will also be italic_
            
            **This text will be bold**
            __This will also be bold__
            
            _You **can** combine them_
            
            ## Lists
            
            Unordered
            
            * Item 1
            * Item 2
              * Item 2a
              * Item 2b
            
            Ordered
            
            1. Item 1
            2. Item 2
            3. Item 3
               * Item 3a
               * Item 3b
            
            ## Images
            
            ![GitHub Logo](/images/logo.png)
            Format: ![Alt Text](url)
            
            ## Links
            
            http://github.com - automatic!
            [GitHub](http://github.com)
            
            ## Blockquotes
            
            As Kanye West said:
            
            > We're living the future so
            > the present is our past.
            
            ## Inline code
            
            I think you should use an
            `<addr>` element here instead.
            
            ## Syntax highlighting
            
            ```javascript
            function fancyAlert(arg) {
              if(arg) {
                $.facebox({div:'#foo'})
              }
            }
            ```
            
            ## Task lists
            
            - [x] @mentions, #refs, [links](), **formatting**, and <del>tags</del> supported
            - [x] list syntax required (any unordered or ordered list supported)
            - [x] this is a complete item
            - [ ] this is an incomplete item
            
            ## Tables
            
            | Syntax      | Description |
            | ----------- | ----------- |
            | Header      | Title       |
            | Paragraph   | Text        |
            
            ## SHA references
            
            Any reference to a commit’s SHA-1 hash will be automatically converted into a link to that commit on GitHub.
            
            ## Issue references within a repository
            
            Any number that refers to an Issue or Pull Request will be automatically converted into a link.
            
            ## Username @mentions

            Typing an `@` symbol, followed by a username, will notify that person to come and view the comment. This is called an “@mention”, because you’re mentioning the individual. You can also @mention teams within an organization.
            
            ## Automatic linking for URLs
            
            Any URL (like http://www.github.com/) will be automatically converted into a clickable link.
            
            ## Strikethrough
            
            Any word wrapped with two tildes (like ~~this~~) will appear crossed out.
            
            ## Emoji
            
            GitHub supports emoji!
            To see a list of every image we support, check out the Emoji Cheat Sheet.
            
            ## Task Lists
            
            - [x] This is a complete item
            - [ ] This is an incomplete item
            
            ## Nested Lists
            
            1. First list item
               - First nested list item
                 - Second nested list item
            MD;

            return ($this->convert($markdown));
        }, 1000);
    }

    protected function convert(string $markdown): string
    {
        return preg_replace('/<(.|\n)*?>/', ' ', Str::markdown($markdown));
    }
}
