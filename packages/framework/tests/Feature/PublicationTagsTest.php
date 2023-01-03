<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Features\Publications\Models\PublicationTags;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Features\Publications\Models\PublicationTags
 */
class PublicationTagsTest extends TestCase
{
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

        $this->assertSame(['bar', 'baz'], PublicationTags::getValuesForTagName('foo')->toArray());
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

        $this->assertSame([], PublicationTags::getValuesForTagName('bar')->toArray());
    }

    public function testGetValuesForTagNameWithNoTags()
    {
        $this->assertSame([], PublicationTags::getValuesForTagName('foo')->toArray());
    }
}
