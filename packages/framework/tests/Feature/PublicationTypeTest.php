<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use function array_merge;
use function array_reverse;
use Hyde\Framework\Features\Publications\Models\PaginationSettings;
use Hyde\Framework\Features\Publications\Models\PublicationFieldDefinition;
use Hyde\Framework\Features\Publications\Models\PublicationListPage;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Framework\Features\Publications\Paginator;
use Hyde\Framework\Features\Publications\PublicationService;
use Hyde\Hyde;
use Hyde\Pages\PublicationPage;
use Hyde\Testing\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ItemNotFoundException;
use RuntimeException;

/**
 * @covers \Hyde\Framework\Features\Publications\Models\PublicationType
 */
class PublicationTypeTest extends TestCase
{
    public function test_can_construct_new_publication_type()
    {
        $publicationType = new PublicationType(...$this->getTestData());

        foreach ($this->getTestData() as $key => $property) {
            if ($key === 'pagination') {
                $this->assertEquals($property, $publicationType->$key->toArray());
            } else {
                $this->assertEquals($property, $publicationType->$key);
            }
        }
    }

    public function test_construct_with_default_values()
    {
        $publicationType = new PublicationType('Test Publication');

        $this->assertEquals('Test Publication', $publicationType->name);
        $this->assertEquals('identifier', $publicationType->canonicalField);
        $this->assertEquals('detail.blade.php', $publicationType->detailTemplate);
        $this->assertEquals('list.blade.php', $publicationType->listTemplate);
        $this->assertEquals([], $publicationType->fields);
        $this->assertNull($publicationType->pagination);

        $this->assertEquals('test-publication', $publicationType->getDirectory());
    }

    public function test_construct_with_pagination_object()
    {
        $paginationSettings = PaginationSettings::fromArray([
            'sortField'     => 'title',
            'sortAscending' => false,
            'pageSize'      => 10,
        ]);
        $publicationType = new PublicationType('Test Publication', pagination: $paginationSettings);
        $this->assertSame($paginationSettings, $publicationType->pagination);
    }

    public function test_class_is_arrayable()
    {
        $publicationType = new PublicationType(...$this->getTestData());
        $this->assertSame($this->getTestData(), $publicationType->toArray());
    }

    public function test_class_is_json_serializable()
    {
        $publicationType = new PublicationType(...$this->getTestData());
        $this->assertSame(json_encode($this->getTestData()), json_encode($publicationType));
    }

    public function test_class_is_jsonable()
    {
        $publicationType = new PublicationType(...$this->getTestData());
        $this->assertSame(json_encode($this->getTestData(), 128), $publicationType->toJson());
    }

    public function test_get_directory()
    {
        $publicationType = new PublicationType(...$this->getTestDataWithPathInformation());
        $this->assertSame('test-publication', $publicationType->getDirectory());
    }

    public function test_get_identifier()
    {
        $publicationType = new PublicationType(...$this->getTestDataWithPathInformation());
        $this->assertSame('test-publication', $publicationType->getIdentifier());
    }

    public function test_get_identifier_with_no_directory()
    {
        $publicationType = new PublicationType(...$this->getTestData());
        $this->assertSame('test-publication', $publicationType->getIdentifier());
    }

    public function test_can_save_to_json_file()
    {
        $publicationType = new PublicationType(...$this->getTestDataWithPathInformation());
        $publicationType->save();

        $this->assertFileExists('test-publication/schema.json');
        $this->assertSame(json_encode($this->getTestData(), 128), file_get_contents(Hyde::path('test-publication/schema.json')));

        File::deleteDirectory(Hyde::path('test-publication'));
    }

    public function test_can_save_to_json_file_using_custom_path()
    {
        $publicationType = new PublicationType(...$this->getTestData());
        $publicationType->save('test-publication/foo.json');

        $this->assertFileExists('test-publication/foo.json');
        $this->assertSame(json_encode($this->getTestData(), 128), file_get_contents(Hyde::path('test-publication/foo.json')));

        File::deleteDirectory(Hyde::path('test-publication'));
    }

    public function test_can_load_from_json_file()
    {
        $publicationType = new PublicationType(...array_merge($this->getTestData(), [
            'directory' => 'tests/fixtures',
        ]));

        $this->assertEquals($publicationType, PublicationType::fromFile(('tests/fixtures/test-publication-schema.json')));
    }

    public function test_it_loads_arbitrary_publication_fields_from_schema_file()
    {
        $this->directory('test-publication');
        $fields = [
            [
                'name' => 'Title',
                'type' => 'text',
                'identifier' => 'title',
                'required' => true,
            ],
            [
                'name' => 'Body',
                'type' => 'markdown',
                'identifier' => 'body',
                'required' => true,
            ],
        ];
        $this->file('test-publication/schema.json', json_encode([
            'name' => 'Test Publication',
            'fields' => $fields,
        ]));

        $publicationType = PublicationType::fromFile('test-publication/schema.json');
        $this->assertSame($fields, $publicationType->fields);
    }

    public function test_get_fields_method_returns_collection_of_field_objects()
    {
        $publicationType = new PublicationType(...$this->getTestDataWithPathInformation());
        $collection = $publicationType->getFields();
        $this->assertCount(1, $collection);
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertInstanceOf(PublicationFieldDefinition::class, $collection->first());
        $this->assertEquals(new Collection([
            'title' => new PublicationFieldDefinition('string', 'title'),
        ]), $collection);
    }

    public function test_get_field_method_parses_publication_fields_from_schema_file()
    {
        $this->directory('test-publication');
        $this->file('test-publication/schema.json', json_encode([
            'name' => 'Test Publication',
            'fields' => [
                ['name' => 'title', 'type' => 'string'],
                ['name' => 'number', 'type' => 'integer'],
            ],
        ]));

        $publicationType = PublicationType::fromFile('test-publication/schema.json');
        $this->assertEquals(new Collection([
            'title' => new PublicationFieldDefinition('string', 'title'),
            'number' => new PublicationFieldDefinition('integer', 'number'),
        ]), $publicationType->getFields());
    }

    public function test_get_field_method_parses_publication_fields_with_option_properties_from_schema_file()
    {
        $this->directory('test-publication');
        $this->file('test-publication/schema.json', json_encode([
            'name' => 'Test Publication',
            'fields' => [
                ['name' => 'title', 'type' => 'string', 'rules' => ['foo', 'bar']],
                ['name' => 'tags', 'type' => 'tag', 'tagGroup' => 'myTags'],
            ],
        ]));

        $publicationType = PublicationType::fromFile('test-publication/schema.json');
        $this->assertEquals(new Collection([
            'title' => new PublicationFieldDefinition('string', 'title', ['foo', 'bar']),
            'tags' => new PublicationFieldDefinition('tag', 'tags', tagGroup: 'myTags'),
        ]), $publicationType->getFields());
    }

    public function test_get_field_data_returns_field_data()
    {
        $publicationType = new PublicationType(...$this->getTestData());

        $this->assertSame([['name' => 'title', 'type' => 'string']], $publicationType->getFieldData());

        $publicationType->fields = [];

        $this->assertSame([], $publicationType->getFieldData());
    }

    public function test_get_method_can_find_existing_file_on_disk()
    {
        $publicationType = new PublicationType(...$this->getTestDataWithPathInformation());
        $publicationType->save();

        $this->assertEquals($publicationType, PublicationType::get('test-publication'));
        File::deleteDirectory(Hyde::path('test-publication'));
    }

    public function test_get_method_fails_if_publication_type_does_not_exist()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Could not parse schema file '.('missing/schema.json'));
        PublicationType::get('missing');
    }

    public function test_get_list_page()
    {
        $publicationType = new PublicationType(...$this->getTestDataWithPathInformation());
        $this->assertEquals(new PublicationListPage($publicationType), $publicationType->getListPage());
    }

    public function testGetFieldDefinition()
    {
        $publicationType = new PublicationType(...$this->getTestData());
        $this->assertEquals(PublicationFieldDefinition::fromArray([
            'name' => 'title',
            'type' => 'string',
        ]), $publicationType->getFieldDefinition('title'));
    }

    public function testGetFieldDefinitionWithMissingField()
    {
        $publicationType = new PublicationType(...$this->getTestData());

        $this->expectException(ItemNotFoundException::class);
        $publicationType->getFieldDefinition('missing');
    }

    public function testGetCanonicalFieldDefinition()
    {
        $publicationType = new PublicationType(...$this->getTestData());
        $this->assertEquals(PublicationFieldDefinition::fromArray([
            'name' => 'title',
            'type' => 'string',
        ]), $publicationType->getCanonicalFieldDefinition());
    }

    public function testGetCanonicalFieldDefinitionWithMetaFieldAsCanonical()
    {
        $publicationType = new PublicationType(...$this->getTestData(['canonicalField' => '__createdAt']));
        $this->assertEquals(PublicationFieldDefinition::fromArray([
            'name' => '__createdAt',
            'type' => 'string',
        ]), $publicationType->getCanonicalFieldDefinition());
    }

    public function testGetPublications()
    {
        $publicationType = new PublicationType(...$this->getTestData());
        $this->assertEquals(
            PublicationService::getPublicationsForPubType($publicationType),
            $publicationType->getPublications()
        );
    }

    public function testGetPaginator()
    {
        $publicationType = new PublicationType(...$this->getTestData());
        $this->assertEquals(
            (new Paginator(paginationRouteBasename: 'test-publication')),
            $publicationType->getPaginator()
        );
    }

    public function testGetPaginatorWithCustomPublicationTypePaginationSettings()
    {
        $publicationType = new PublicationType(...$this->getTestData([
            'pagination' => [
                'pageSize' => 10,
            ],
        ]));
        $this->assertEquals(
            (new Paginator(pageSize: 10, paginationRouteBasename: 'test-publication')),
            $publicationType->getPaginator()
        );
    }

    public function testGetPaginatorSortsCollectionBySpecifiedPaginationSettings()
    {
        $this->directory('test-publication');

        $paginationSettings = new PaginationSettings('myNumber');
        $fields = [['name' => 'myNumber', 'type' => 'integer']];

        $publicationType = new PublicationType('test-publication', 'myNumber', pagination: $paginationSettings, fields: $fields);
        $publicationType->save();

        $pages[0] = (new PublicationPage('test-publication/page-1', ['myNumber' => 5], type: $publicationType))->save();
        $pages[1] = (new PublicationPage('test-publication/page-2', ['myNumber' => 4], type: $publicationType))->save();
        $pages[2] = (new PublicationPage('test-publication/page-3', ['myNumber' => 3], type: $publicationType))->save();
        $pages[3] = (new PublicationPage('test-publication/page-4', ['myNumber' => 2], type: $publicationType))->save();
        $pages[4] = (new PublicationPage('test-publication/page-5', ['myNumber' => 1], type: $publicationType))->save();

        $this->assertEquals(
            (new Paginator(array_reverse($pages), paginationRouteBasename: 'test-publication')),
            $publicationType->getPaginator()
        );
    }

    public function testGetPaginatorSortsCollectionBySpecifiedPaginationSettingsWithDescendingSort()
    {
        $this->directory('test-publication');

        $paginationSettings = new PaginationSettings('myNumber', false);
        $fields = [['name' => 'myNumber', 'type' => 'integer']];

        $publicationType = new PublicationType('test-publication', 'myNumber', pagination: $paginationSettings, fields: $fields);
        $publicationType->save();

        $pages[0] = (new PublicationPage('test-publication/page-1', ['myNumber' => 5], type: $publicationType))->save();
        $pages[1] = (new PublicationPage('test-publication/page-2', ['myNumber' => 4], type: $publicationType))->save();
        $pages[2] = (new PublicationPage('test-publication/page-3', ['myNumber' => 3], type: $publicationType))->save();
        $pages[3] = (new PublicationPage('test-publication/page-4', ['myNumber' => 2], type: $publicationType))->save();
        $pages[4] = (new PublicationPage('test-publication/page-5', ['myNumber' => 1], type: $publicationType))->save();

        $this->assertEquals(
            (new Paginator($pages, paginationRouteBasename: 'test-publication')),
            $publicationType->getPaginator()
        );
    }

    public function testUsesPaginationReturnsTrueWhenPaginationShouldBeEnabled()
    {
        $this->directory('test-publication');
        $publicationType = new PublicationType(...$this->getTestData([
            'pagination' => [
                'pageSize' => 1,
            ],
        ]));
        $publicationType->save();

        $this->file('test-publication/1.md');
        $this->file('test-publication/2.md');

        $this->assertTrue($publicationType->usesPagination());
    }

    public function testUsesPaginationReturnsFalseWhenPageSizeIsSetToNought()
    {
        $this->directory('test-publication');
        $publicationType = new PublicationType(...$this->getTestData([
            'pagination' => [
                'pageSize' => 0,
            ],
        ]));
        $publicationType->save();

        $this->file('test-publication/1.md');
        $this->file('test-publication/2.md');

        $this->assertFalse($publicationType->usesPagination());
    }

    public function testUsesPaginationReturnsFalseWhenNumberOfPagesIsLessThanPageSize()
    {
        $this->directory('test-publication');
        $publicationType = new PublicationType(...$this->getTestData([
            'pagination' => [
                'pageSize' => 2,
            ],
        ]));
        $publicationType->save();

        $this->file('test-publication/1.md');
        $this->file('test-publication/2.md');

        $this->assertFalse($publicationType->usesPagination());
    }

    public function testArrayRepresentationWithDefaultValues()
    {
        $publicationType = new PublicationType('test-publication');

        $this->assertSame([
            'name' => 'test-publication',
            'canonicalField' => 'identifier',
            'detailTemplate' => 'detail.blade.php',
            'listTemplate' => 'list.blade.php',
            'fields' => [],
        ], $publicationType->toArray());
    }

    public function testJsonRepresentationWithDefaultValues()
    {
        $publicationType = new PublicationType('test-publication');

        $this->assertSame(<<<'JSON'
            {
                "name": "test-publication",
                "canonicalField": "identifier",
                "detailTemplate": "detail.blade.php",
                "listTemplate": "list.blade.php",
                "fields": []
            }
            JSON, $publicationType->toJson());
    }

    public function testArrayRepresentationWithPaginationSettings()
    {
        $publicationType = new PublicationType('test-publication', pagination: new PaginationSettings());

        $this->assertSame([
            'name' => 'test-publication',
            'canonicalField' => 'identifier',
            'detailTemplate' => 'detail.blade.php',
            'listTemplate' => 'list.blade.php',
            'pagination' => [
                'sortField' => '__createdAt',
                'sortAscending' => true,
                'pageSize' => 25,
            ],
            'fields' => [],
        ], $publicationType->toArray());
    }

    public function testJsonRepresentationWithPaginationSettings()
    {
        $publicationType = new PublicationType('test-publication', pagination: new PaginationSettings());

        $this->assertSame(<<<'JSON'
            {
                "name": "test-publication",
                "canonicalField": "identifier",
                "detailTemplate": "detail.blade.php",
                "listTemplate": "list.blade.php",
                "pagination": {
                    "sortField": "__createdAt",
                    "sortAscending": true,
                    "pageSize": 25
                },
                "fields": []
            }
            JSON, $publicationType->toJson());
    }

    protected function getTestData(array $mergeData = []): array
    {
        return array_merge([
            'name' => 'Test Publication',
            'canonicalField' => 'title',
            'detailTemplate' => 'detail.blade.php',
            'listTemplate' => 'list.blade.php',
            'pagination' => [
                'sortField' => '__createdAt',
                'sortAscending' => true,
                'pageSize' => 25,
            ],
            'fields' => [
                [
                    'name' => 'title',
                    'type' => 'string',
                ],
            ],
        ], $mergeData);
    }

    protected function getTestDataWithPathInformation(): array
    {
        return array_merge($this->getTestData(), [
            'directory' => 'test-publication',
        ]);
    }
}
