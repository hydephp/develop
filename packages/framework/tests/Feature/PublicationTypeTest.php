<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Testing\TestCase;

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
}
