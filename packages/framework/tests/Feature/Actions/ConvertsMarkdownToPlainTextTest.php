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
    public function test_should_leave_a_string_alone_without_markdown()
    {
        $string = 'PHP developers are the best.';
        $this->assertSame($string, $this->removeMd($string));
    }

    public function test_should_strip_out_remaining_markdown()
    {
        $string = '*PHP* developers are the _best_.';
        $expected = 'PHP developers are the best.';
        $this->assertSame($expected, $this->removeMd($string));
    }

    public function test_should_leave_non_matching_markdown_markdown()
    {
        $string = '*PHP* developers* are the _best_.';
        $expected = 'PHP developers* are the best.';
        $this->assertSame($expected, $this->removeMd($string));
    }

    public function test_should_leave_non_matching_markdown_but_strip_empty_anchors()
    {
        $string = '*PHP* [developers]()* are the _best_.';
        $expected = 'PHP developers* are the best.';
        $this->assertSame($expected, $this->removeMd($string));
    }

    public function test_should_strip_html()
    {
        $string = '<p>Hello World</p>';
        $expected = 'Hello World';
        $this->assertSame($expected, $this->removeMd($string));
    }

    public function test_should_strip_anchors()
    {
        $string = '*PHP* [developers](https://engineering.condenast.io/)* are the _best_.';
        $expected = 'PHP developers* are the best.';
        $this->assertSame($expected, $this->removeMd($string));
    }

    public function test_should_strip_img_tags()
    {
        $string = '![](https://placebear.com/640/480)*PHP* developers are the _best_.';
        $expected = 'PHP developers are the best.';
        $this->assertSame($expected, $this->removeMd($string));
    }

    public function test_should_use_the_alt_text_of_an_image_iit_is_provided()
    {
        $string = '![This is the alt-text](https://www.example.com/images/logo.png)';
        $expected = 'This is the alt-text';
        $this->assertSame($expected, $this->removeMd($string));
    }

    public function test_should_strip_code_tags()
    {
        $string = 'In `Getting Started` we set up `something` foo.';
        $expected = 'In Getting Started we set up something foo.';
        $this->assertSame($expected, $this->removeMd($string));
    }

    public function test_should_leave_hashtags_in_headings()
    {
        $string = '## This #heading contains #hashtags';
        $expected = 'This #heading contains #hashtags';
        $this->assertSame($expected, $this->removeMd($string));
    }

    public function test_should_remove_emphasis()
    {
        $string = 'Italicized an *I* _made_ me *sad*.';
        $expected = 'Italicized an I made me sad.';
        $this->assertSame($expected, $this->removeMd($string));
    }

    public function test_should_remove_emphasis_only_if_there_is_no_space_between_word_and_emphasis_characters()
    {
        $string = 'There should be no _space_, *before* *closing * _emphasis character _.';
        $expected = 'There should be no space, before *closing * _emphasis character _.';
        $this->assertSame($expected, $this->removeMd($string));
    }

    public function test_should_remove_emphasis_only_if_there_is_space_before_opening_and_after_closing_emphasis_characters(
    )
    {
        $string = '._Spaces_ _ before_ and _after _ emphasised character results in no emphasis.';
        $expected = '.Spaces _ before_ and _after _ emphasised character results in no emphasis.';
        $this->assertSame($expected, $this->removeMd($string));
    }

    public function test_should_remove_double_emphasis()
    {
        $string = '**this sentence has __double styling__**';
        $expected = 'this sentence has double styling';
        $this->assertSame($expected, $this->removeMd($string));
    }

    public function test_should_remove_horizontal_rules()
    {
        $string = "Some text on a line\n\n---\n\nA line below";
        $expected = "Some text on a line\n\nA line below";
        $this->assertSame($expected, $this->removeMd($string));
    }

    public function test_should_remove_horizontal_rules_with_space_separated_asterisks()
    {
        $string = "Some text on a line\n\n* * *\n\nA line below";
        $expected = "Some text on a line\n\nA line below";
        $this->assertSame($expected, $this->removeMd($string));
    }

    public function test_should_remove_blockquotes()
    {
        $string = '>I am a blockquote';
        $expected = 'I am a blockquote';
        $this->assertSame($expected, $this->removeMd($string));
    }

    public function test_should_remove_blockquotes_with_spaces()
    {
        $string = '> I am a blockquote';
        $expected = 'I am a blockquote';
        $this->assertSame($expected, $this->removeMd($string));
    }

    public function test_should_remove_indented_blockquotes()
    {
        $tests = [
            ' > I am a blockquote' => 'I am a blockquote' ,
            '  > I am a blockquote' => 'I am a blockquote' ,
            '   > I am a blockquote' => 'I am a blockquote' ,
	    ];
        foreach ($tests as $string => $expected) {
            $this->assertSame($expected, $this->removeMd($string));
        }
    }

    public function test_should_remove_blockquotes_over_multiple_lines()
    {
        $string = "> I am a blockquote first line  \n>I am a blockquote second line";
        $expected = "I am a blockquote first line\nI am a blockquote second line";
        $this->assertSame($expected, $this->removeMd($string));
    }

    public function test_should_not_remove_greater_than_signs()
    {
        $tests = [
            '100 > 0' =>  '100 > 0',
            '100 >= 0' =>  '100 >= 0',
            '100>0' =>  '100>0',
            '> 100 > 0' =>  '100 > 0',
            '1 < 100' =>  '1 < 100',
            '1 <= 100' =>  '1 <= 100',
	    ];
        foreach ($tests as $string => $expected) {
            $this->assertSame($expected, $this->removeMd($string));
        }
    }

    public function test_should_strip_unordered_list_leaders()
    {
        $string = "Some text on a line\n\n* A lisItem\n* Another list item";
        $expected = "Some text on a line\n\nA lisItem\nAnother list item";
        $this->assertSame($expected, $this->removeMd($string));
    }

    public function test_should_strip_ordered_list_leaders()
    {
        $string = "Some text on a line\n\n9. A lisItem\n10. Another list item";
        $expected = "Some text on a line\n\nA lisItem\nAnother list item";
        $this->assertSame($expected, $this->removeMd($string));
    }

    public function test_should_handle_paragraphs_with_markdown()
    {
        $paragraph = '
## This is a heading ##

This is a paragraph with [a link](https://www.disney.com/).

### This is another heading

In `Getting Started` we set up `something` foo.

  * Some list
  * With items
    * Even indented';

        $expected = '
This is a heading

This is a paragraph with a link.

This is another heading

In Getting Started we set up something foo.

  Some list
  With items
    Even indented';
        $this->assertSame($expected, $this->removeMd($paragraph));
    }

    public function test_should_not_strip_paragraphs_without_content()
    {
        $paragraph = "\n#This paragraph\n##This paragraph#";
        $expected = $paragraph;
        $this->assertSame($expected, $this->removeMd($paragraph));
    }

    protected function removeMd(string $markdown): string
    {
        return (new ConvertsMarkdownToPlainText($markdown))->execute();
    }
}
