<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\PublicationFieldValueTests;

use DateTime;
use Hyde\Framework\Features\Publications\Models\PublicationFieldValues\DatetimeField;
use Hyde\Framework\Features\Publications\PublicationFieldTypes;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Features\Publications\Models\PublicationFieldValues\DatetimeField
 */
class DatetimeFieldValueTest extends TestCase
{
    public function testConstruct()
    {
        $value = new DatetimeField('2023-01-01');
        $this->assertEquals(new DateTime('2023-01-01'), $value->getValue());
    }

    public function testGetValue()
    {
        $value = new DatetimeField('2023-01-01');
        $this->assertEquals(new DateTime('2023-01-01'), $value->getValue());
    }

    public function testGetType()
    {
        $this->assertSame(DatetimeField::TYPE, DatetimeField::getType());
        $this->assertSame(PublicationFieldTypes::Datetime, DatetimeField::getType());
    }

    public function testParseInput()
    {
        $value = DatetimeField::parseInput('2023-01-01');
        $this->assertEquals(new DateTime('2023-01-01'), $value);
    }

    public function testToYamlType()
    {
        $value = DatetimeField::toYamlType(new DateTime('2023-01-01'));
        $this->assertEquals(new DateTime('2023-01-01'), $value);
    }
}
