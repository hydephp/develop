<?php

declare(strict_types=1);

namespace Hyde\Support;

/**
 * @see \Hyde\Framework\Testing\Feature\ReadingTimeTest
 */
class ReadingTime
{
    protected const WORDS_PER_MINUTE = 200;

    protected readonly string $text;
    protected int $seconds;

    public function __construct(string $text)
    {
        $this->text = $text;
    }
}
