<?php

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Models\MarkdownDocument;
use Hyde\Testing\TestCase;

/**
 * Class HasDynamicTitleTest.
 *
 * @covers \Hyde\Framework\Concerns\HasDynamicTitle
 */
class HasDynamicTitleTest extends TestCase
{
    protected array $matter;

    public function test_can_find_title_from_front_matter()
    {
        $document = new MarkdownDocument([
            'title' => 'My Title',
        ], body: '');

        $this->assertEquals('My Title', $document->findTitleForDocument());
    }

    public function test_can_find_title_from_h1_tag()
    {
        $document = new MarkdownDocument([], body: '# My Title');

        $this->assertEquals('My Title', $document->findTitleForDocument());
    }

    public function test_can_find_title_from_slug()
    {
        $document = new MarkdownDocument([], body: '', slug: 'my-title');
        $this->assertEquals('My Title', $document->findTitleForDocument());
    }
}
