<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Features\Publications\Models\PublicationTags;
use Hyde\Testing\TestCase;
use Illuminate\Support\Collection;

/**
 * @covers \Hyde\Framework\Features\Publications\Models\PublicationTags
 */
class PublicationTagsTest extends TestCase
{
    public function testCanAddTags()
    {
        $tags = new PublicationTags();
        $tags->addTag('test', ['test1', 'test2']);

        $this->assertEquals(new Collection([
            'test' => ['test1', 'test2'],
        ]), $tags->getTags());
    }

    public function testCanAddTagsWithSingleValue()
    {
        $tags = new PublicationTags();
        $tags->addTag('test', 'test1');

        $this->assertEquals(new Collection([
            'test' => ['test1'],
        ]), $tags->getTags());
    }

    public function testGetAllTags()
    {
        $tags = [
            'foo' => [
                'bar',
                'baz',
            ],
        ];
        $this->file('tags.json', json_encode($tags));
        $this->assertSame($tags, PublicationTags::getAllTags()->toArray());
    }

    public function testGetAllTagsWithNoTags()
    {
        $this->assertSame([], PublicationTags::getAllTags()->toArray());
    }

    public function testGetValuesForTagName()
    {
        $tags = [
            'foo' => [
                'bar',
                'baz',
            ],
            'bar' => [
                'baz',
                'qux',
            ],
        ];

        $this->file('tags.json', json_encode($tags));

        $this->assertSame(['bar', 'baz'], PublicationTags::getValuesForTagName('foo'));
    }

    public function testGetValuesForTagNameWithMissingTagName()
    {
        $tags = [
            'foo' => [
                'bar',
                'baz',
            ],
        ];

        $this->file('tags.json', json_encode($tags));

        $this->assertSame([], PublicationTags::getValuesForTagName('bar'));
    }

    public function testGetValuesForTagNameWithNoTags()
    {
        $this->assertSame([], PublicationTags::getValuesForTagName('foo'));
    }
}
