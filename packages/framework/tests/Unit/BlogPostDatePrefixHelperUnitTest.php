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
    protected static bool $needsConfig = true;

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
        DatePrefixHelper::extractDate('my-post.md');
    }

    public function testExtractDateWithInvalidDatePrefixFormat()
    {
        $this->expectException(InvalidArgumentException::class);
        DatePrefixHelper::extractDate('2024-11-XX-my-post.md');
    }

    public function testExtractDateWithMalformedTime()
    {
        $this->expectException(InvalidArgumentException::class);
        DatePrefixHelper::extractDate('2024-11-05-25-99-my-post.md');
    }

    public function testExtractDateWithMinimalPadding()
    {
        $date = DatePrefixHelper::extractDate('2024-1-5-my-post.md');
        $this->assertInstanceOf(DateTime::class, $date);
        $this->assertSame('2024-01-05 00:00', $date->format('Y-m-d H:i'));
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
}
