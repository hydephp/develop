<?php

declare(strict_types=1);

use Hyde\Testing\UnitTestCase;
use Hyde\Framework\Features\Blogging\DatePrefixHelper;

/**
 * @covers \Hyde\Framework\Features\Blogging\DatePrefixHelper
 *
 * @see \Hyde\Framework\Testing\Feature\BlogPostDatePrefixHelperTest
 */
class BlogPostDatePrefixHelperUnitTest extends UnitTestCase
{
    public function testHasDatePrefixWithValidDateOnly()
    {
        $this->assertTrue(DatePrefixHelper::hasDatePrefix('2024-11-05-my-post.md'));
    }

    public function testHasDatePrefixWithValidDateAndTime()
    {
        $this->assertTrue(DatePrefixHelper::hasDatePrefix('2024-11-05-10-30-my-post.md'));
    }

    public function testHasDatePrefixWithoutDatePrefix()
    {
        $this->assertFalse(DatePrefixHelper::hasDatePrefix('my-post.md'));
    }

    public function testHasDatePrefixWithInvalidDateFormat()
    {
        $this->assertFalse(DatePrefixHelper::hasDatePrefix('2024-123-05-my-post.md')); // Invalid month
        $this->assertFalse(DatePrefixHelper::hasDatePrefix('2024-11-123-my-post.md')); // Invalid day
        $this->assertFalse(DatePrefixHelper::hasDatePrefix('2024-11-my-post.md')); // Missing day
        $this->assertFalse(DatePrefixHelper::hasDatePrefix('202-11-05-my-post.md')); // Invalid year format
    }

    public function testHasDatePrefixWithInvalidTimeFormat()
    {
        // These are all true, because the parser will think that the time is part of the slug, so we can't reliably detect these cases, as there is technically a *date* prefix
        $this->assertTrue(DatePrefixHelper::hasDatePrefix('2024-11-05-123-00-my-post.md')); // Invalid hour
        $this->assertTrue(DatePrefixHelper::hasDatePrefix('2024-11-05-10-123-my-post.md')); // Invalid minute
        $this->assertTrue(DatePrefixHelper::hasDatePrefix('2024-11-05-1030-my-post.md')); // Missing hyphen between hour and minute
    }

    public function testHasDatePrefixWithNoDatePrefixButSimilarPattern()
    {
        $this->assertFalse(DatePrefixHelper::hasDatePrefix('hello-2024-11-05.md')); // Date-like pattern in the middle
        $this->assertFalse(DatePrefixHelper::hasDatePrefix('2024/11/05-my-post.md')); // Date separated by slashes
        $this->assertFalse(DatePrefixHelper::hasDatePrefix('11-05-2024-my-post.md')); // Date in non-ISO format
        $this->assertFalse(DatePrefixHelper::hasDatePrefix('post-2024-11-05.md')); // Date-like pattern at the end
    }

    public function testHasDatePrefixWithExtraCharactersAroundDate()
    {
        $this->assertFalse(DatePrefixHelper::hasDatePrefix('x2024-11-05-my-post.md')); // Extra character at start
        $this->assertTrue(DatePrefixHelper::hasDatePrefix('2024-11-05-my-post-.md')); // Extra hyphen at end
        $this->assertTrue(DatePrefixHelper::hasDatePrefix('2024-11-05-my-post123.md')); // Extra numbers after filename
    }

    public function testExtractDateWithValidDateOnly()
    {
        $date = DatePrefixHelper::extractDate('2024-11-05-my-post.md');
        $this->assertInstanceOf(DateTime::class, $date);
        $this->assertSame('2024-11-05 00:00', $date->format('Y-m-d H:i'));
    }

    public function testExtractDateWithValidDateAndTime()
    {
        $date = DatePrefixHelper::extractDate('2024-11-05-10-30-my-post.md');
        $this->assertInstanceOf(DateTime::class, $date);
        $this->assertSame('2024-11-05 10:30', $date->format('Y-m-d H:i'));
    }

    public function testExtractDateWithoutDatePrefix()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The given filepath does not contain a valid ISO 8601 date prefix.');

        DatePrefixHelper::extractDate('my-post.md');
    }

    public function testExtractDateWithInvalidDatePrefixFormat()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The given filepath does not contain a valid ISO 8601 date prefix.');

        DatePrefixHelper::extractDate('2024-11-XX-my-post.md');
    }

    public function testExtractDateWithMalformedTime()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Failed to parse time string (2024-11-05 25:99)');

        DatePrefixHelper::extractDate('2024-11-05-25-99-my-post.md');
    }

    public function testStripDatePrefixWithDateOnly()
    {
        $result = DatePrefixHelper::stripDatePrefix('2024-11-05-my-post.md');
        $this->assertSame('my-post.md', $result);
    }

    public function testStripDatePrefixWithDateAndTime()
    {
        $result = DatePrefixHelper::stripDatePrefix('2024-11-05-10-30-my-post.md');
        $this->assertSame('my-post.md', $result);
    }

    public function testStripDatePrefixWithoutDatePrefix()
    {
        $result = DatePrefixHelper::stripDatePrefix('my-post.md');
        $this->assertSame('my-post.md', $result);
    }

    public function testExtractDateWithUnusualCharactersInFilename()
    {
        $date = DatePrefixHelper::extractDate('2024-11-05-special_chars-#post.md');
        $this->assertSame('2024-11-05 00:00', $date->format('Y-m-d H:i'));
    }

    public function testExtractDateWithAlternativeExtensions()
    {
        $date = DatePrefixHelper::extractDate('2024-11-05-my-post.markdown');
        $this->assertSame('2024-11-05 00:00', $date->format('Y-m-d H:i'));
    }

    public function testEdgeCaseWithExtraHyphens()
    {
        $date = DatePrefixHelper::extractDate('2024-11-05-extra-hyphens-in-title.md');
        $this->assertSame('2024-11-05 00:00', $date->format('Y-m-d H:i'));
    }

    public function testStripDatePrefixRetainsHyphensInTitle()
    {
        $result = DatePrefixHelper::stripDatePrefix('2024-11-05-extra-hyphens-in-title.md');
        $this->assertSame('extra-hyphens-in-title.md', $result);
    }

    public function testInvalidSingleDigitMonthOrDay()
    {
        $this->assertFalse(DatePrefixHelper::hasDatePrefix('2024-1-5-my-post.md')); // Single-digit month and day
    }

    public function testFileWithValidDatePrefixButInvalidExtension()
    {
        $this->assertTrue(DatePrefixHelper::hasDatePrefix('2024-11-05-my-post.txt')); // Unsupported file extension
    }

    public function testTimePrefixWithLeadingZeroInHourOrMinute()
    {
        $date = DatePrefixHelper::extractDate('2024-11-05-00-30-my-post.md');
        $this->assertSame('2024-11-05 00:30', $date->format('Y-m-d H:i'));

        $date = DatePrefixHelper::extractDate('2024-11-05-10-00-my-post.md');
        $this->assertSame('2024-11-05 10:00', $date->format('Y-m-d H:i'));
    }

    public function testFilenameWithPotentiallyMisleadingHyphens()
    {
        $date = DatePrefixHelper::extractDate('2024-11-05-extra-hyphens---title.md');
        $this->assertSame('2024-11-05 00:00', $date->format('Y-m-d H:i'));
    }

    public function testLeapYearDate()
    {
        $date = DatePrefixHelper::extractDate('2024-02-29-my-leap-year-post.md');
        $this->assertSame('2024-02-29 00:00', $date->format('Y-m-d H:i'));
    }

    public function testInvalidDates()
    {
        $date = DatePrefixHelper::extractDate('2024-04-31-my-post.md');
        $this->assertSame('2024-05-01 00:00', $date->format('Y-m-d H:i'));
    }

    public function testStripDateWithVariousUnconventionalExtensions()
    {
        $result = DatePrefixHelper::stripDatePrefix('2024-11-05-my-post.md');
        $this->assertSame('my-post.md', $result);

        $result = DatePrefixHelper::stripDatePrefix('2024-11-05-my-post.markdown');
        $this->assertSame('my-post.markdown', $result);

        $result = DatePrefixHelper::stripDatePrefix('2024-11-05-my-post.txt'); // Should still strip but ignore extension
        $this->assertSame('my-post.txt', $result);
    }
}
