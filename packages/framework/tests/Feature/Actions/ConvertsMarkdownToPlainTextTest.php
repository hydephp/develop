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

    protected function convert(string $markdown): string
    {
        return (new ConvertsMarkdownToPlainText($markdown))->execute();
    }
}
