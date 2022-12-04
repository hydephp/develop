<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use function array_merge;
use Hyde\Framework\Features\Publications\Models\PublicationFieldType;
use Hyde\Framework\Features\Publications\Models\PublicationListPage;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
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
            $this->assertEquals($property, $publicationType->$key);
        }
    }

    public function testConstructWithDefaultValues()
    {
        $publicationType = new PublicationType('Test Publication');

        $this->assertEquals('Test Publication', $publicationType->name);
        $this->assertEquals('identifier', $publicationType->canonicalField);
        $this->assertEquals('__createdAt', $publicationType->sortField);
        $this->assertEquals('DESC', $publicationType->sortDirection);
        $this->assertEquals(25, $publicationType->pageSize);
        $this->assertEquals(true, $publicationType->prevNextLinks);
        $this->assertEquals('detail', $publicationType->detailTemplate);
        $this->assertEquals('list', $publicationType->listTemplate);
        $this->assertEquals([], $publicationType->fields);

        $this->assertEquals('test-publication', $publicationType->getDirectory());
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

    public function test_get_fields_method_returns_collection_of_field_objects()
    {
        $publicationType = new PublicationType(...$this->getTestDataWithPathInformation());
        $collection = $publicationType->getFields();
        $this->assertCount(1, $collection);
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertInstanceOf(PublicationFieldType::class, $collection->first());
        $this->assertEquals(new Collection([
            'title' => new PublicationFieldType('string', 'title', 0, 128),
        ]), $collection);
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

    protected function getTestData(): array
    {
        return [
            'name'           => 'Test Publication',
            'canonicalField' => 'title',
            'sortField'      => '__createdAt',
            'sortDirection'  => 'DESC',
            'pageSize'       => 25,
            'prevNextLinks'  => true,
            'detailTemplate' => 'test-publication_detail',
            'listTemplate'   => 'test-publication_list',
            'fields'         => [
                [
                    'name' => 'title',
                    'min'  => '0',
                    'max'  => '128',
                    'type' => 'string',
                ],
            ],
        ];
    }

    protected function getTestDataWithPathInformation(): array
    {
        return array_merge($this->getTestData(), [
            'directory' => 'test-publication',
        ]);
    }
}
