<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Support\ReadingTime;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Support\ReadingTime
 */
class ReadingTimeTest extends TestCase
{
    protected function words(int $words): string
    {
        return implode(' ', array_fill(0, $words, 'word'));
    }
}
