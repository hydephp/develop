<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

use Hyde\Hyde;
use Hyde\Publications\Models\PublicationTags;
use Hyde\Publications\Models\PublicationType;
use Hyde\Publications\Pages\PublicationPage;
use Hyde\Testing\TestCase;

use function json_encode;

/**
 * @covers \Hyde\Publications\Models\PublicationTags
 */
class PublicationTagsTest extends TestCase
{
    public function testCanGetTagsUsedInPublications()
    {
        $type = new PublicationType('test-publication', fields: [[
            'name' => 'tag',
            'type' => 'tag',
        ]]);

        $page = new PublicationPage(matter: [
            'tag' => ['foo', 'bar'],
        ], type: $type);

        Hyde::kernel()->pages()->addPage($page);
        
        $this->assertSame(['foo', 'bar'], PublicationTags::all());
    }

    public function testMultipleOccurringTagsAreAggregatedUniquely()
    {
        $type = new PublicationType('test-publication', fields: [[
            'name' => 'tag',
            'type' => 'tag',
        ]]);

        Hyde::kernel()->pages()->addPage(new PublicationPage('1', [
            'tag' => ['foo', 'bar'],
        ], type: $type));

        Hyde::kernel()->pages()->addPage(new PublicationPage('2', [
            'tag' => ['foo', 'baz'],
        ], type: $type));

        $this->assertSame(['foo', 'bar', 'baz'], PublicationTags::all());
    }

    public function testAllTagsMethodFindsBothArrayAndSingleTagValues()
    {
        $type = new PublicationType('test-publication', fields: [[
            'name' => 'tag',
            'type' => 'tag',
        ]]);

        Hyde::kernel()->pages()->addPage(new PublicationPage('1', [
            'tag' => 'foo',
        ], type: $type));

        Hyde::kernel()->pages()->addPage(new PublicationPage('2', [
            'tag' => ['bar', 'baz'],
        ], type: $type));

        $this->assertSame(['foo', 'bar', 'baz'], PublicationTags::all());
    }

    // CSV?

    public function testAllTagsMethodReturnsEmptyArrayWhenThereAreNoTagsUsed()
    {
        $this->assertSame([], PublicationTags::all());
    }
    
    public function canConstructNewTagsInstance()
    {
        $this->assertInstanceOf(PublicationTags::class, new PublicationTags());
    }

    public function testConstructorAutomaticallyLoadsTagsFile()
    {
        $this->file('tags.yml', json_encode(['foo' => ['bar', 'baz']]));

        $this->assertSame(['foo' => ['bar', 'baz']], (new PublicationTags())->getTags());
    }

    public function testConstructorAddsEmptyArrayWhenThereIsNoTagsFile()
    {
        $this->assertEquals([], (new PublicationTags())->getTags());
    }

    public function testGetTags()
    {
        $this->file('tags.yml', json_encode(['foo' => ['bar', 'baz']]));

        $this->assertEquals(['foo' => ['bar', 'baz']], (new PublicationTags())->getTags());
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
        ], PublicationTags::getAllTags());
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
        ], PublicationTags::getAllTags());
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
        $this->assertSame($tags, PublicationTags::getAllTags());
    }

    public function testGetAllTagsWithNoTags()
    {
        $this->assertSame([], PublicationTags::getAllTags());
    }
}
