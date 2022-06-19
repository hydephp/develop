<?php

namespace Hyde\Testing\Framework\Unit;

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

    public function testCanFindTitleFromFrontMatter()
    {
        $document = new MarkdownDocument([
            'title' => 'My Title',
        ], body: '');

        $this->assertEquals('My Title', $document->findTitleForDocument());
    }

    public function testCanFindTitleFromH1Tag()
    {
        $document = new MarkdownDocument([], body: '# My Title');

        $this->assertEquals('My Title', $document->findTitleForDocument());
    }

    public function testCanFindTitleFromSlug()
    {
        $document = new MarkdownDocument([], body: '', slug: 'my-title');
        $this->assertEquals('My Title', $document->findTitleForDocument());
    }
}
