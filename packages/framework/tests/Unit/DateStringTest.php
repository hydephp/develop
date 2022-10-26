<?php
declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use DateTime;
use Hyde\Framework\Models\Support\DateString;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Hyde\Framework\Models\Support\DateString
 */
class DateStringTest extends TestCase
{
    public function test_it_can_parse_date_string()
    {
        $dateString = new DateString('2020-01-01');
        $this->assertEquals('2020-01-01', $dateString->string);
    }

    public function test_it_can_parse_date_string_into_datetime_object()
    {
        $dateString = new DateString('2020-01-01 UTC');
        $this->assertInstanceOf(DateTime::class, $dateString->dateTimeObject);
    }

    public function test_it_can_format_date_string_into_machine_readable_string()
    {
        $dateString = new DateString('2020-01-01 UTC');
        $this->assertEquals('2020-01-01T00:00:00+00:00', $dateString->datetime);
    }

    public function test_it_can_format_date_string_into_human_readable_string()
    {
        $dateString = new DateString('2020-01-01 UTC');
        $this->assertEquals('Wednesday Jan 1st, 2020, at 12:00am', $dateString->sentence);
    }

    public function test_it_can_format_date_string_into_short_human_readable_string()
    {
        $dateString = new DateString('2020-01-01 UTC');
        $this->assertEquals('Jan 1st, 2020', $dateString->short);
    }

    public function test_it_can_format_date_string_into_short_human_readable_string_using_magic_method()
    {
        $dateString = new DateString('2022-01-01 UTC');
        $this->assertEquals('Jan 1st, 2022', (string) $dateString);
    }
}
