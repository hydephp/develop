<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Actions;

use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Actions\ConvertsMarkdownToPlainText
 */
class ConvertsMarkdownToPlainTextTest extends TestCase
{
    public function testActionConvertsMarkdownToPlainText()
    {
        //
    }

    protected function getMarkdown(): string
    {
        return <<<MARKDOWN
            <!-- https://www.markdownguide.org/basic-syntax (CC BY-SA 4.0.) distilled into a usable file containing all the basic syntax options from the Markdown spec  -->
            
            <!-- Headings -->
            # Heading level 1
            ## Heading level 2	
            ### Heading level 3	
            #### Heading level 4	
            ##### Heading level 5	
            ###### Heading level 6	
            
            <!-- Headings Alternate Syntax -->
            Alt Heading level 1
            ===================
            
            Alt Heading level 2
            -------------------
            
            <!-- Paragraphs -->
            I really like using Markdown.
            
            I think I'll use it to format all of my documents from now on.
            
            <!-- Paragraphs -->
            This is the first line.  
            And this is the second line.
            
            <!-- Line Breaks -->
            First line with two spaces after.  
            And the next line.
            
            First line with the HTML tag after.<br>
            And the next line.
            
            <!-- Bold -->
            I just love **bold text**.	
            I just love __bold text__.	
            Love**is**bold	
            
            <!-- Italic -->
            Italicized text is the *cat's meow*.
            Italicized text is the _cat's meow_.
            A*cat*meow
            
            <!-- Bold and Italic -->
            This text is ***really important***.
            This text is ___really important___.
            This text is __*really important*__.
            This text is **_really important_**.
            This is really***very***important text.
            
            <!-- Blockquotes -->
            > Dorothy followed her through many of the beautiful rooms in her castle.
            
            <!-- Blockquotes with Multiple Paragraphs -->
            > Dorothy followed her through many of the beautiful rooms in her castle.
            >
            > The Witch bade her clean the pots and kettles and sweep the floor and keep the fire fed with wood.
            
            <!-- Nested Blockquotes -->
            > Dorothy followed her through many of the beautiful rooms in her castle.
            >
            >> The Witch bade her clean the pots and kettles and sweep the floor and keep the fire fed with wood.
            
            <!-- Blockquotes with Other Elements -->
            > #### The quarterly results look great!
            >
            > - Revenue was off the chart.
            > - Profits were higher than ever.
            >
            >  *Everything* is going according to **plan**.
            
            <!-- Ordered Lists -->
            1. First item
            2. Second item
            3. Third item
            4. Fourth item
            
            1. First item
            1. Second item
            1. Third item
            1. Fourth item
            
            1. First item
            8. Second item
            3. Third item
            5. Fourth item
            
            1. First item
            2. Second item
            3. Third item
                1. Indented item
                2. Indented item
            4. Fourth item
            
            <!-- Unordered Lists -->
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
            
            <!-- Starting Unordered List Items With Numbers -->
            - 1968\. A great year!
            - I think 1969 was second best.
            
            <!-- Adding Elements in Lists -->
            * This is the first list item.
            * Here's the second list item.
            
                I need to add another paragraph below the second list item.
            
            * And here's the third list item.
            
            
            * This is the first list item.
            * Here's the second list item.
            
                > A blockquote would look great below the second list item.
            
            * And here's the third list item.
            
            
            1. Open the file.
            2. Find the following code block on line 21:
            
                    <html>
                      <head>
                        <title>Test</title>
                      </head>
            
            3. Update the title to match the name of your website.
            
            
            1. Open the file containing the Linux mascot.
            2. Marvel at its beauty.
            
                ![Tux, the Linux mascot](/assets/images/tux.png)
            
            3. Close the file.
            
            
            1. First item
            2. Second item
            3. Third item
                - Indented item
                - Indented item
            4. Fourth item
            
            
            <!-- Code -->
            At the command prompt, type `nano`.
            
            <!-- Escaping Backticks -->
            ``Use `code` in your Markdown file.``
            
            <!-- Code Blocks -->
                <html>
                  <head>
                  </head>
                </html>
            
            <!-- Horizontal Rules -->
            ***
            
            ---
            
            _________________
            
            <!-- Links -->
            My favorite search engine is [Duck Duck Go](https://duckduckgo.com).
            
            My favorite search engine is [Duck Duck Go](https://duckduckgo.com "The best search engine for privacy").
            
            <https://www.markdownguide.org>
            <fake@example.com>
            
            I love supporting the **[EFF](https://eff.org)**.
            This is the *[Markdown Guide](https://www.markdownguide.org)*.
            See the section on [`code`](#code).
            
            [link](https://www.example.com/my%20great%20page)
            
            <a href="https://www.example.com/my great page">link</a>
            
            <!-- Images -->
            ![The San Juan Mountains are beautiful!](/assets/images/san-juan-mountains.jpg "San Juan Mountains")
            
            <!-- Linking Images -->
            [![An old rock in the desert](/assets/images/shiprock.jpg "Shiprock, New Mexico by Beau Rogers")](https://www.flickr.com/photos/beaurogers/31833779864/in/photolist-Qv3rFw-34mt9F-a9Cmfy-5Ha3Zi-9msKdv-o3hgjr-hWpUte-4WMsJ1-KUQ8N-deshUb-vssBD-6CQci6-8AFCiD-zsJWT-nNfsgB-dPDwZJ-bn9JGn-5HtSXY-6CUhAL-a4UTXB-ugPum-KUPSo-fBLNm-6CUmpy-4WMsc9-8a7D3T-83KJev-6CQ2bK-nNusHJ-a78rQH-nw3NvT-7aq2qf-8wwBso-3nNceh-ugSKP-4mh4kh-bbeeqH-a7biME-q3PtTf-brFpgb-cg38zw-bXMZc-nJPELD-f58Lmo-bXMYG-bz8AAi-bxNtNT-bXMYi-bXMY6-bXMYv)
            
            <!-- Escaping Characters -->
            \* Without the backslash, this would be a bullet in an unordered list.
            
            <!-- HTML -->
            This **word** is bold. This <em>word</em> is italic.
            MARKDOWN;
    }
}
