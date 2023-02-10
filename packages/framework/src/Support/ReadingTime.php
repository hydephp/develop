<?php

declare(strict_types=1);

namespace Hyde\Support;

/**
 * @see \Hyde\Framework\Testing\Feature\ReadingTimeTest
 */
class ReadingTime
{
    /** @var int How many words per minute is read. Inversely proportional. Increase for a shorter reading time. */
    protected const WORDS_PER_MINUTE = 200;

    protected readonly string $text;
    protected int $seconds;

    public function __construct(string $text)
    {
        $this->text = $text;

        $this->generate();
    }

    public function getSeconds(): int
    {
        // TODO: Implement getSeconds() method
    }

    public function getMinutes(): int
    {
        // TODO: Implement getMinutes() method
    }

    public function getFormatted(): string
    {
        // TODO: Implement getFormatted() method
    }

    protected function generate(): void
    {
        // TODO: Implement generate() method
    }
}
