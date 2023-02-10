<?php

declare(strict_types=1);

namespace Hyde\Support;

use Hyde\Facades\Filesystem;

/**
 * Calculate the estimated reading time for a text.
 *
 * @see \Hyde\Framework\Testing\Feature\ReadingTimeTest
 */
class ReadingTime
{
    /** @var int How many words per minute is read. Inversely proportional. Increase for a shorter reading time. */
    protected const WORDS_PER_MINUTE = 240;

    /** @var string The text to calculate the reading time for. */
    protected readonly string $text;

    /** @var int The number of words in the text. */
    protected int $wordCount;

    /** @var float The number of seconds it takes to read the text. */
    protected float $seconds;

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

    public function getWordCount(): int
    {
        return $this->wordCount;
    }

    protected function generate(): void
    {
        $wordCount = str_word_count($this->text);

        $minutes = $wordCount / static::WORDS_PER_MINUTE;
        $seconds = $minutes * 60;

        $this->wordCount = $wordCount;
        $this->seconds = $seconds;
    }

    public static function fromString(string $text): static
    {
        return new static($text);
    }

    public static function fromFile(string $path): static
    {
        return new static(Filesystem::getContents($path));
    }
}
