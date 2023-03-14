<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

use Hyde\Framework\Exceptions\FileNotFoundException;
use Hyde\Hyde;
use Hyde\Publications\Models\PublicationTags;
use Hyde\Testing\TestCase;
use Illuminate\Support\Collection;

use function json_encode;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

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

    public function testGetTagsInGroup()
    {
        $this->file('tags.yml', json_encode(['foo' => ['bar', 'baz']]));

        $this->assertEquals(['bar', 'baz'], (new PublicationTags())->getTagsInGroup('foo'));
    }

    public function testGetTagsInGroupOnlyReturnTagsForTheSpecifiedGroup()
    {
        $this->file('tags.yml', json_encode([
            'foo' => ['bar', 'baz'],
            'bar' => ['foo', 'baz'],
        ]));

        $this->assertEquals(['foo', 'baz'], (new PublicationTags())->getTagsInGroup('bar'));
    }

    public function testGetTagsInGroupReturnsEmptyArrayWhenGroupDoesNotExist()
    {
        $this->file('tags.yml', json_encode(['foo' => ['bar', 'baz']]));

        $this->assertEquals([], (new PublicationTags())->getTagsInGroup('bar'));
    }

    public function testCanAddTagGroup()
    {
        $tags = new PublicationTags();
        $tags->addTagGroup('test', ['test1', 'test2']);

        $this->assertEquals(new Collection([
            'test' => ['test1', 'test2'],
        ]), $tags->getTags());
    }

    public function testCanAddTagGroupWithSingleValue()
    {
        $tags = new PublicationTags();
        $tags->addTagGroup('test', 'test1');

        $this->assertEquals(new Collection([
            'test' => ['test1'],
        ]), $tags->getTags());
    }

    public function testCanAddMultipleTagGroups()
    {
        $expected = new PublicationTags();
        $expected->addTagGroup('test', ['test1', 'test2']);
        $expected->addTagGroup('test2', ['test3', 'test4']);

        $tags = new PublicationTags();
        $tags->addTagGroups([
            'test' => ['test1', 'test2'],
            'test2' => ['test3', 'test4'],
        ]);

        $this->assertEquals($expected->getTags(), $tags->getTags());
        $this->assertSame([
            'test' => ['test1', 'test2'],
            'test2' => ['test3', 'test4'],
        ], $tags->getTags()->toArray());
    }

    public function testCanAddTagsToExistingGroup()
    {
        $tags = new PublicationTags();
        $tags->addTagGroup('test', ['foo']);
        $tags->addTagsToGroup('test', ['bar', 'baz']);

        $this->assertEquals(new Collection([
            'test' => ['foo', 'bar', 'baz'],
        ]), $tags->getTags());
    }

    public function testCanAddSingleTagToExistingGroup()
    {
        $tags = new PublicationTags();
        $tags->addTagGroup('test', ['foo']);
        $tags->addTagsToGroup('test', 'bar');

        $this->assertEquals(new Collection([
            'test' => ['foo', 'bar'],
        ]), $tags->getTags());
    }

    public function testGetTagGroups()
    {
        $tags = new PublicationTags();
        $tags->addTagGroup('test', ['foo']);
        $tags->addTagGroup('test2', ['bar']);
        $tags->save();

        $this->assertSame(['test', 'test2'], PublicationTags::getTagGroups());
        unlink(Hyde::path('tags.yml'));
    }

    public function testGetTagGroupsWithNoTags()
    {
        $this->assertSame([], PublicationTags::getTagGroups());
    }

    public function testCanSaveTagsToDisk()
    {
        $tags = new PublicationTags();
        $tags->addTagGroup('test', ['test1', 'test2']);
        $tags->save();

        $this->assertSame(
            <<<'YAML'
            test:
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

        $this->file('tags.yml', json_encode($tags));

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

        $this->file('tags.yml', json_encode($tags));

        $this->assertSame([], PublicationTags::getValuesForTagName('bar'));
    }

    public function testGetValuesForTagNameWithNoTags()
    {
        $this->assertSame([], PublicationTags::getValuesForTagName('foo'));
    }

    public function testValidateTagsFileWithValidFile()
    {
        $this->file('tags.yml', json_encode(['foo' => ['bar', 'baz']]));

        PublicationTags::validateTagsFile();
        $this->assertTrue(true);
    }

    public function testValidateTagsFileWithInvalidFile()
    {
        $this->file('tags.yml', 'invalid yaml');

        $this->expectException(ParseException::class);
        PublicationTags::validateTagsFile();
    }

    public function testValidateTagsFileWithNoFile()
    {
        $this->expectException(FileNotFoundException::class);
        PublicationTags::validateTagsFile();
    }

    public function testValidateTagsFileWithEmptyYamlFile()
    {
        $this->file('tags.yml', Yaml::dump([]));

        $this->expectException(ParseException::class);
        PublicationTags::validateTagsFile();
    }
}
