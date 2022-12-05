<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Features\Publications\Models\PaginationSettings;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Features\Publications\Models\PaginationSettings
 */
class PaginationSettingsTest extends TestCase
{
    public function testConstructWithDefaultValues()
    {
        $paginationSettings = new PaginationSettings();

        $this->assertSame('__createdAt', $paginationSettings->sortField);
        $this->assertSame(true, $paginationSettings->sortAscending);
        $this->assertSame(25, $paginationSettings->pageSize);
        $this->assertSame(true, $paginationSettings->prevNextLinks);
    }

    public function testConstruct()
    {
        $paginationSettings = new PaginationSettings('foo', false, 10, false);

        $this->assertSame('foo', $paginationSettings->sortField);
        $this->assertFalse($paginationSettings->sortAscending);
        $this->assertSame(10, $paginationSettings->pageSize);
        $this->assertFalse($paginationSettings->prevNextLinks);
    }

    public function testFromArray()
    {
        $paginationSettings = PaginationSettings::fromArray([
            'sortField' => 'foo',
            'sortAscending' => false,
            'pageSize' => 10,
            'prevNextLinks' => false,
        ]);

        $this->assertSame('foo', $paginationSettings->sortField);
        $this->assertSame(false, $paginationSettings->sortAscending);
        $this->assertSame(10, $paginationSettings->pageSize);
        $this->assertSame(false, $paginationSettings->prevNextLinks);
    }

    public function testToArray()
    {
        $paginationSettings = new PaginationSettings();

        $this->assertSame([
            'sortField' => '__createdAt',
            'sortAscending' => true,
            'pageSize' => 25,
            'prevNextLinks' => true,
        ], $paginationSettings->toArray());
    }

    public function testToJson()
    {
        $paginationSettings = new PaginationSettings();

        $this->assertSame('{"sortField":"__createdAt","sortAscending":true,"pageSize":25,"prevNextLinks":true}', $paginationSettings->toJson());
    }

    public function testJsonSerialize()
    {
        $paginationSettings = new PaginationSettings();

        $this->assertSame($paginationSettings->toArray(), $paginationSettings->jsonSerialize());
    }
}
