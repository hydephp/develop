<?php

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Concerns\HasTableOfContents;
use Hyde\Testing\TestCase;

/**
 * Class HasTableOfContentsTest.
 *
 * @covers \Hyde\Framework\Concerns\HasTableOfContents
 *
 * @see \Hyde\Framework\Testing\Feature\Actions\GeneratesSidebarTableOfContentsTest
 */
class HasTableOfContentsTest extends TestCase
{
    use HasTableOfContents;

    protected string $body;

    public function testHasTableOfContentsProperty()
    {
        $this->assertClassHasAttribute('tableOfContents', static::class);
    }

    public function testConstructorCreatesTableOfContentsString()
    {
        $this->body = '## Title';
        $this->constructTableOfContents();
        $this->assertIsString($this->tableOfContents);
        $this->assertEquals('<ul class="table-of-contents"><li><a href="#title">Title</a></li></ul>', str_replace("\n", '', $this->tableOfContents));
    }
}
