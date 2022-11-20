<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Helpers;

use function array_merge;

/**
 * @internal
 */
trait TestsPublications
{
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
