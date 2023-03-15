<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use DateTime;
use Hyde\Support\Models\DateString;
use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Support\Models\DateString
 */
class DateStringTest extends UnitTestCase
{
    public function testItCanParseDateString()
    {
        $dateString = new DateString('2020-01-01');
        $this->assertEquals('2020-01-01', $dateString->string);
    }

    public function testItCanParseDateStringIntoDatetimeObject()
    {
        $dateString = new DateString('2020-01-01 UTC');
        $this->assertInstanceOf(DateTime::class, $dateString->dateTimeObject);
    }

    public function testItCanFormatDateStringIntoMachineReadableString()
    {
        $dateString = new DateString('2020-01-01 UTC');
        $this->assertEquals('2020-01-01T00:00:00+00:00', $dateString->datetime);
    }

    public function testItCanFormatDateStringIntoHumanReadableString()
    {
        $dateString = new DateString('2020-01-01 UTC');
        $this->assertEquals('Wednesday Jan 1st, 2020, at 12:00am', $dateString->sentence);
    }

    public function testItCanFormatDateStringIntoShortHumanReadableString()
    {
        $dateString = new DateString('2020-01-01 UTC');
        $this->assertEquals('Jan 1st, 2020', $dateString->short);
    }

    public function testItCanFormatDateStringIntoShortHumanReadableStringUsingMagicMethod()
    {
        $dateString = new DateString('2022-01-01 UTC');
        $this->assertEquals('Jan 1st, 2022', (string) $dateString);
    }
}
