<?php

declare(strict_types=1);

namespace Hyde\Support;

/**
 * @see \Hyde\Framework\Testing\Feature\ReadingTimeTest
 */
class ReadingTime
{
    protected const WORDS_PER_MINUTE = 200;

    protected string $text;
    protected int $seconds;
}
