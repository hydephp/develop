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
        $this->assertSame('2020-01-01', $dateString->string);
    }

    public function testItCanParseDateStringIntoDatetimeObject()
    {
        $dateString = new DateString('2020-01-01 UTC');
        $this->assertInstanceOf(DateTime::class, $dateString->dateTimeObject);
    }

    public function testItCanFormatDateStringIntoMachineReadableString()
    {
        $dateString = new DateString('2020-01-01 UTC');
        $this->assertSame('2020-01-01T00:00:00+00:00', $dateString->datetime);
    }

    public function testItCanFormatDateStringIntoHumanReadableString()
    {
        $dateString = new DateString('2020-01-01 UTC');
        $this->assertSame('Wednesday Jan 1st, 2020, at 12:00am', $dateString->sentence);
    }

    public function testItCanFormatDateStringIntoShortHumanReadableString()
    {
        $dateString = new DateString('2020-01-01 UTC');
        $this->assertSame('Jan 1st, 2020', $dateString->short);
    }

    public function testItCanFormatDateStringIntoShortHumanReadableStringUsingMagicMethod()
    {
        $dateString = new DateString('2022-01-01 UTC');
        $this->assertSame('Jan 1st, 2022', (string) $dateString);
    }

    public function testItCanForwardGetTimestampMethodToDateTimeObject()
    {
        $dateString = new DateString('2020-01-01 00:00:00 UTC');
        $expected = (new DateTime('2020-01-01 00:00:00 UTC'))->getTimestamp();
        $this->assertSame($expected, $dateString->getTimestamp());
    }

    public function testItCanForwardModifyMethodToDateTimeObject()
    {
        $dateString = new DateString('2020-01-01 UTC');
        $modified = $dateString->modify('+1 day');
        $this->assertInstanceOf(DateTime::class, $modified);
        $this->assertSame('2020-01-02', $modified->format('Y-m-d'));
    }

    public function testItCanForwardSetTimeMethodToDateTimeObject()
    {
        $dateString = new DateString('2020-01-01 UTC');
        $modified = $dateString->setTime(15, 30);
        $this->assertInstanceOf(DateTime::class, $modified);
        $this->assertSame('15:30:00', $modified->format('H:i:s'));
    }

    public function testCallingUndefinedMethodThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Method nonExistentMethod does not exist on the DateTime object.');

        $dateString = new DateString('2020-01-01 UTC');
        $dateString->nonExistentMethod();
    }
}
