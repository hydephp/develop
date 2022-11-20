<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\File;

/**
 * @covers \Hyde\Framework\Features\Publications\Models\PublicationType
 */
class PublicationTypeTest extends TestCase
{
    public function testCanConstructNewPublicationType()
    {
        $publicationType = new PublicationType(...$this->getTestData());

        foreach ($this->getTestData() as $key => $property) {
            $this->assertEquals($property, $publicationType->$key);
        }
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

    public function testGetDirectory()
    {
        $publicationType = new PublicationType(...$this->getTestDataWithPathInformation());
        $this->assertSame('test-publication', $publicationType->getDirectory());
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
    }

    protected function getTestData(): array
    {
        return [
            'name'           => 'test',
            'canonicalField' => 'canonical',
            'sortField'      => 'sort',
            'sortDirection'  => 'asc',
            'pagesize'       => 10,
            'prevNextLinks'  => true,
            'detailTemplate' => 'detail',
            'listTemplate'   => 'list',
            'fields'         => [
                'foo' => 'bar',
            ]
        ];
    }

    protected function getTestDataWithPathInformation(): array
    {
        return array_merge($this->getTestData(), [
            'directory' => 'test-publication',
        ]);
    }
}
