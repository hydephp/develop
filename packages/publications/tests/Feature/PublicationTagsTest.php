<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

use Hyde\Hyde;
use Hyde\Publications\Models\PublicationTags;
use Hyde\Testing\TestCase;
use Illuminate\Support\Collection;

use function json_encode;

/**
 * @covers \Hyde\Publications\Models\PublicationTags
 */
class PublicationTagsTest extends TestCase
{
    public function canConstructNewTagsInstance()
    {
        $this->assertInstanceOf(PublicationTags::class, new PublicationTags());
    }

    public function testConstructorAutomaticallyLoadsTagsFile()
    {
        $this->file('tags.yml', json_encode(['foo' => ['bar', 'baz']]));

        $this->assertSame(['foo' => ['bar', 'baz']], (new PublicationTags())->getTags()->toArray());
    }

    public function testConstructorAddsEmptyArrayWhenThereIsNoTagsFile()
    {
        $this->assertEquals(new Collection(), (new PublicationTags())->getTags());
    }

    public function testGetTags()
    {
        $this->file('tags.yml', json_encode(['foo' => ['bar', 'baz']]));

        $this->assertEquals(new Collection(['foo' => ['bar', 'baz']]), (new PublicationTags())->getTags());
    }

    public function testCanSaveTagsToDisk()
    {
        $tags = new PublicationTags();
        $tags->addTags(['test1', 'test2']);
        $tags->save();

        $this->assertSame(
            <<<'YAML'
            - test1
            - test2

            YAML, file_get_contents(Hyde::path('tags.yml'))
        );

        unlink(Hyde::path('tags.yml'));
    }

    public function testCanLoadTagsFromJsonFile()
    {
        $this->file('tags.yml', <<<'JSON'
            {
                "Foo": [
                    "one",
                    "two",
                    "three"
                ],
                "Second": [
                    "foo",
                    "bar",
                    "baz"
                ]
            }
            JSON
        );

        $this->assertSame([
            'Foo' => ['one', 'two', 'three'],
            'Second' => ['foo', 'bar', 'baz'],
        ], PublicationTags::getAllTags()->toArray());
    }

    public function testCanLoadTagsFromYamlFile()
    {
        $this->file('tags.yml', <<<'YAML'
            Foo:
                - one
                - two
                - three
            Second:
                - foo
                - bar
                - baz
            YAML
        );

        $this->assertSame([
            'Foo' => ['one', 'two', 'three'],
            'Second' => ['foo', 'bar', 'baz'],
        ], PublicationTags::getAllTags()->toArray());
    }

    public function testGetAllTags()
    {
        $tags = [
            'foo' => [
                'bar',
                'baz',
            ],
        ];
        $this->file('tags.yml', json_encode($tags));
        $this->assertSame($tags, PublicationTags::getAllTags()->toArray());
    }

    public function testGetAllTagsWithNoTags()
    {
        $this->assertSame([], PublicationTags::getAllTags()->toArray());
    }
}
