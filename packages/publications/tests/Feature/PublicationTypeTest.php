<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

use Hyde\Hyde;
use Hyde\Publications\Models\PublicationFieldDefinition;
use Hyde\Publications\Models\PublicationType;
use Hyde\Publications\Pages\PublicationListPage;
use Hyde\Publications\Pages\PublicationPage;
use Hyde\Publications\Publications;
use Hyde\Support\Paginator;
use Hyde\Testing\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ItemNotFoundException;
use RuntimeException;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Publications\Models\PublicationType::class)]
class PublicationTypeTest extends TestCase
{
    public function testCanConstructNewPublicationType()
    {
        $publicationType = new PublicationType(...$this->getTestData());

        foreach ($this->getTestData() as $key => $property) {
            if ($key === 'pagination') {
                $this->assertEquals($property, $publicationType->$key->toArray());
            } else {
                if ($key === 'fields') {
                    $this->assertEquals($property, $publicationType->$key->toArray());
                } else {
                    $this->assertEquals($property, $publicationType->$key);
                }
            }
        }
    }

    public function testConstructWithDefaultValues()
    {
        $publicationType = new PublicationType('Test Publication');

        $this->assertSame('Test Publication', $publicationType->name);
        $this->assertSame('__createdAt', $publicationType->canonicalField);
        $this->assertSame('detail.blade.php', $publicationType->detailTemplate);
        $this->assertSame('list.blade.php', $publicationType->listTemplate);
        $this->assertEquals(collect([]), $publicationType->fields);
        $this->assertSame('__createdAt', $publicationType->sortField);
        $this->assertEquals(true, $publicationType->sortAscending);
        $this->assertSame(0, $publicationType->pageSize);

        $this->assertSame('test-publication', $publicationType->getDirectory());
    }

    public function testConstructWithPaginationSettings()
    {
        $paginationSettings = [
            'sortField' => 'title',
            'sortAscending' => false,
            'pageSize' => 10,
        ];
        $publicationType = new PublicationType('Test Publication', ...$paginationSettings);

        $this->assertSame('title', $publicationType->sortField);
        $this->assertFalse($publicationType->sortAscending);
        $this->assertSame(10, $publicationType->pageSize);
    }

    public function testClassIsArrayable()
    {
        $publicationType = new PublicationType(...$this->getTestData());
        $this->assertSame($this->getTestData(), $publicationType->toArray());
    }

    public function testClassIsJsonSerializable()
    {
        $publicationType = new PublicationType(...$this->getTestData());
        $this->assertSame(json_encode($this->getTestData()), json_encode($publicationType));
    }

    public function testClassIsJsonable()
    {
        $publicationType = new PublicationType(...$this->getTestData());
        $this->assertSame(json_encode($this->getTestData(), 128), $publicationType->toJson());
    }

    public function testGetDirectory()
    {
        $publicationType = new PublicationType(...$this->getTestDataWithPathInformation());
        $this->assertSame('test-publication', $publicationType->getDirectory());
    }

    public function testGetIdentifier()
    {
        $publicationType = new PublicationType(...$this->getTestDataWithPathInformation());
        $this->assertSame('test-publication', $publicationType->getIdentifier());
    }

    public function testGetIdentifierWithNoDirectory()
    {
        $publicationType = new PublicationType(...$this->getTestData());
        $this->assertSame('test-publication', $publicationType->getIdentifier());
    }

    public function testCanSaveToJsonFile()
    {
        $publicationType = new PublicationType(...$this->getTestDataWithPathInformation());
        $publicationType->save();

        $this->assertFileExists('test-publication/schema.json');
        $this->assertSame(json_encode($this->getTestData(), 128), file_get_contents(Hyde::path('test-publication/schema.json')));

        File::deleteDirectory(Hyde::path('test-publication'));
    }

    public function testCanSaveToJsonFileUsingCustomPath()
    {
        $publicationType = new PublicationType(...$this->getTestData());
        $publicationType->save('test-publication/foo.json');

        $this->assertFileExists('test-publication/foo.json');
        $this->assertSame(json_encode($this->getTestData(), 128), file_get_contents(Hyde::path('test-publication/foo.json')));

        File::deleteDirectory(Hyde::path('test-publication'));
    }

    public function testCanLoadFromJsonFile()
    {
        $this->directory('test-publication');

        $this->file('test-publication/schema.json', <<<'JSON'
            {
                "name": "Test Publication",
                "canonicalField": "title",
                "detailTemplate": "detail.blade.php",
                "listTemplate": "list.blade.php",
                "sortField": "__createdAt",
                "sortAscending": true,
                "pageSize": 25,
                "fields": [
                    {
                        "name": "title",
                        "type": "string"
                    }
                ]
            }
            JSON
        );

        $publicationType = new PublicationType(...$this->getTestData());

        $this->assertEquals($publicationType, PublicationType::fromFile('test-publication/schema.json'));
    }

    public function testCanLoadFieldsWithValidationRules()
    {
        $this->directory('test-publication');
        $fields = [
            [
                'type' => 'text',
                'name' => 'title',
                'rules' => ['required'],
            ],
        ];
        $this->file('test-publication/schema.json', json_encode([
            'name' => 'Test Publication',
            'fields' => $fields,
        ]));

        $publicationType = PublicationType::fromFile('test-publication/schema.json');
        $this->assertSame($fields, $publicationType->getFields()->toArray());
    }

    public function testGetFieldsMethodReturnsCollectionOfFieldObjects()
    {
        $publicationType = new PublicationType(...$this->getTestDataWithPathInformation());
        $collection = $publicationType->getFields();
        $this->assertCount(1, $collection);
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertInstanceOf(PublicationFieldDefinition::class, $collection->first());
        $this->assertEquals(new Collection([
            new PublicationFieldDefinition('string', 'title'),
        ]), $collection);
    }

    public function testGetFieldMethodParsesPublicationFieldsFromSchemaFile()
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
            new PublicationFieldDefinition('string', 'title'),
            new PublicationFieldDefinition('integer', 'number'),
        ]), $publicationType->getFields());
    }

    public function testGetFieldMethodParsesPublicationFieldsWithOptionPropertiesFromSchemaFile()
    {
        $this->directory('test-publication');
        $this->file('test-publication/schema.json', json_encode([
            'name' => 'Test Publication',
            'fields' => [
                ['name' => 'title', 'type' => 'string', 'rules' => ['foo', 'bar']],
                ['name' => 'tags', 'type' => 'tag'],
            ],
        ]));

        $publicationType = PublicationType::fromFile('test-publication/schema.json');
        $this->assertEquals(new Collection([
            new PublicationFieldDefinition('string', 'title', ['foo', 'bar']),
            new PublicationFieldDefinition('tag', 'tags'),
        ]), $publicationType->getFields());
    }

    public function testGetMethodCanFindExistingFileOnDisk()
    {
        $publicationType = new PublicationType(...$this->getTestDataWithPathInformation());
        $publicationType->save();

        $this->assertEquals($publicationType, PublicationType::get('test-publication'));
        File::deleteDirectory(Hyde::path('test-publication'));
    }

    public function testGetMethodFailsIfPublicationTypeDoesNotExist()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Could not parse schema file '.'missing/schema.json');
        PublicationType::get('missing');
    }

    public function testGetListPage()
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
            Publications::getPublicationsForType($publicationType),
            $publicationType->getPublications()
        );
    }

    public function testGetPaginator()
    {
        $publicationType = new PublicationType(...$this->getTestData());
        $this->assertEquals(
            new Paginator(paginationRouteBasename: 'test-publication'),
            $publicationType->getPaginator()
        );
    }

    public function testGetPaginatorWithCustomPublicationTypePaginationSettings()
    {
        $publicationType = new PublicationType(...$this->getTestData([
            'pageSize' => 10,
        ]));
        $this->assertEquals(
            new Paginator(pageSize: 10, paginationRouteBasename: 'test-publication'),
            $publicationType->getPaginator()
        );
    }

    public function testGetPaginatorSortsCollectionBySpecifiedPaginationSettings()
    {
        $this->directory('test-publication');

        $fields = [['name' => 'myNumber', 'type' => 'integer']];

        $publicationType = new PublicationType('test-publication', 'myNumber', sortField: 'myNumber', pageSize: 25, fields: $fields);
        $publicationType->save();

        $pages[0] = (new PublicationPage('test-publication/page-1', ['myNumber' => 5], type: $publicationType))->save();
        $pages[1] = (new PublicationPage('test-publication/page-2', ['myNumber' => 4], type: $publicationType))->save();
        $pages[2] = (new PublicationPage('test-publication/page-3', ['myNumber' => 3], type: $publicationType))->save();
        $pages[3] = (new PublicationPage('test-publication/page-4', ['myNumber' => 2], type: $publicationType))->save();
        $pages[4] = (new PublicationPage('test-publication/page-5', ['myNumber' => 1], type: $publicationType))->save();

        $this->assertEquals(
            new Paginator(array_reverse($pages), paginationRouteBasename: 'test-publication'),
            $publicationType->getPaginator()
        );
    }

    public function testGetPaginatorSortsCollectionBySpecifiedPaginationSettingsWithDescendingSort()
    {
        $this->directory('test-publication');

        $fields = [['name' => 'myNumber', 'type' => 'integer']];

        $publicationType = new PublicationType('test-publication', 'myNumber', sortField: 'myNumber', sortAscending: false, pageSize: 25, fields: $fields);
        $publicationType->save();

        $pages[0] = (new PublicationPage('test-publication/page-1', ['myNumber' => 5], type: $publicationType))->save();
        $pages[1] = (new PublicationPage('test-publication/page-2', ['myNumber' => 4], type: $publicationType))->save();
        $pages[2] = (new PublicationPage('test-publication/page-3', ['myNumber' => 3], type: $publicationType))->save();
        $pages[3] = (new PublicationPage('test-publication/page-4', ['myNumber' => 2], type: $publicationType))->save();
        $pages[4] = (new PublicationPage('test-publication/page-5', ['myNumber' => 1], type: $publicationType))->save();

        $this->assertEquals(
            new Paginator($pages, paginationRouteBasename: 'test-publication'),
            $publicationType->getPaginator()
        );
    }

    public function testUsesPaginationReturnsTrueWhenPaginationShouldBeEnabled()
    {
        $this->directory('test-publication');
        $publicationType = new PublicationType(...$this->getTestData([
            'pageSize' => 1,
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
            'pageSize' => 0,
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
            'pageSize' => 2,
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
            'canonicalField' => '__createdAt',
            'detailTemplate' => 'detail.blade.php',
            'listTemplate' => 'list.blade.php',
            'sortField' => '__createdAt',
            'sortAscending' => true,
            'pageSize' => 0,
            'fields' => [],
        ], $publicationType->toArray());
    }

    public function testJsonRepresentationWithDefaultValues()
    {
        $publicationType = new PublicationType('test-publication');

        $this->assertSame(<<<'JSON'
            {
                "name": "test-publication",
                "canonicalField": "__createdAt",
                "detailTemplate": "detail.blade.php",
                "listTemplate": "list.blade.php",
                "sortField": "__createdAt",
                "sortAscending": true,
                "pageSize": 0,
                "fields": []
            }
            JSON, $publicationType->toJson());
    }

    public function testArrayRepresentationWithMetadata()
    {
        $publicationType = new PublicationType('test-publication', metadata: $metadata = [
            'foo' => ['bar', 'baz'],
            'bar' => 'baz',
            'baz' => 1,
        ]);

        $this->assertSame([
            'name' => 'test-publication',
            'canonicalField' => '__createdAt',
            'detailTemplate' => 'detail.blade.php',
            'listTemplate' => 'list.blade.php',
            'sortField' => '__createdAt',
            'sortAscending' => true,
            'pageSize' => 0,
            'fields' => [],
            'metadata' => $metadata,
        ], $publicationType->toArray());
    }

    public function testJsonRepresentationWithMetadata()
    {
        $publicationType = new PublicationType('test-publication', metadata: [
            'foo' => ['bar', 'baz'],
            'bar' => 'baz',
            'baz' => 1,
        ]);

        $this->assertSame(<<<'JSON'
            {
                "name": "test-publication",
                "canonicalField": "__createdAt",
                "detailTemplate": "detail.blade.php",
                "listTemplate": "list.blade.php",
                "sortField": "__createdAt",
                "sortAscending": true,
                "pageSize": 0,
                "fields": [],
                "metadata": {
                    "foo": [
                        "bar",
                        "baz"
                    ],
                    "bar": "baz",
                    "baz": 1
                }
            }
            JSON, $publicationType->toJson());
    }

    public function testCanParseSchemaFileWithMetadata()
    {
        $this->directory('test-publication');
        $this->file('test-publication/schema.json', <<<'JSON'
            {
                "name": "test-publication",
                "canonicalField": "__createdAt",
                "detailTemplate": "detail.blade.php",
                "listTemplate": "list.blade.php",
                "sortField": "__createdAt",
                "sortAscending": true,
                "pageSize": 0,
                "fields": [],
                "metadata": {
                    "foo": [
                        "bar",
                        "baz"
                    ],
                    "bar": "baz",
                    "baz": 1
                }
            }
            JSON
        );

        $this->assertSame([
            'name' => 'test-publication',
            'canonicalField' => '__createdAt',
            'detailTemplate' => 'detail.blade.php',
            'listTemplate' => 'list.blade.php',
            'sortField' => '__createdAt',
            'sortAscending' => true,
            'pageSize' => 0,
            'fields' => [],
            'metadata' => [
                'foo' => ['bar', 'baz'],
                'bar' => 'baz',
                'baz' => 1,
            ],
        ], PublicationType::get('test-publication')->toArray());
    }

    public function testCanGetMetadata()
    {
        $publicationType = new PublicationType('test-publication', metadata: [
            'foo' => ['bar', 'baz'],
            'bar' => 'baz',
            'baz' => 1,
        ]);

        $this->assertSame([
            'foo' => ['bar', 'baz'],
            'bar' => 'baz',
            'baz' => 1,
        ], $publicationType->getMetadata());
    }

    public function testCanSetMetadata()
    {
        $publicationType = new PublicationType('test-publication');
        $publicationType->setMetadata([
            'foo' => ['bar', 'baz'],
            'bar' => 'baz',
            'baz' => 1,
        ]);

        $this->assertSame([
            'foo' => ['bar', 'baz'],
            'bar' => 'baz',
            'baz' => 1,
        ], $publicationType->getMetadata());
    }

    public function testValidateSchemaFile()
    {
        $this->directory('test-publication');
        $publicationType = new PublicationType('test-publication', fields: [
            ['name' => 'myField', 'type' => 'string'],
        ]);
        $publicationType->save();

        $publicationType->validateSchemaFile();

        $this->assertTrue(true);
    }

    protected function getTestData(array $mergeData = []): array
    {
        return array_merge([
            'name' => 'Test Publication',
            'canonicalField' => 'title',
            'detailTemplate' => 'detail.blade.php',
            'listTemplate' => 'list.blade.php',
            'sortField' => '__createdAt',
            'sortAscending' => true,
            'pageSize' => 25,
            'fields' => [
                [
                    'type' => 'string',
                    'name' => 'title',
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
