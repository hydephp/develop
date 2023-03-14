<?php

declare(strict_types=1);

namespace Hyde\Support;

use Closure;
use Hyde\Facades\Filesystem;
use Stringable;

use function floor;
use function round;
use function sprintf;
use function str_word_count;

/**
 * Calculate the estimated reading time for a text.
 *
 * @see \Hyde\Framework\Testing\Feature\ReadingTimeTest
 */
class ReadingTime implements Stringable
{
    /** @var int How many words per minute is read. Inversely proportional. Increase for a shorter reading time. */
    protected static int $wordsPerMinute = 240;

    /** @var string The text to calculate the reading time for. */
    protected readonly string $text;

    /** @var int The number of words in the text. */
    protected int $wordCount;

    /** @var int The number of seconds it takes to read the text. */
    protected int $seconds;

    public static function fromString(string $text): static
    {
        return new static($text);
    }

    public static function fromFile(string $path): static
    {
        return static::fromString(Filesystem::getContents($path));
    }

    public function __construct(string $text)
    {
        $this->text = $text;

        $this->generate();
    }

    public function __toString()
    {
        return $this->getFormatted();
    }

    public function getWordCount(): int
    {
        return $this->wordCount;
    }

    public function getMinutes(): int
    {
        return (int) floor($this->getMinutesAsFloat());
    }

    public function getSeconds(): int
    {
        return $this->seconds;
    }

    protected function getMinutesAsFloat(): float
    {
        return $this->seconds / 60;
    }

    public function getSecondsOver(): int
    {
        return (int) round(($this->getMinutesAsFloat() - $this->getMinutes()) * 60);
    }

    public function getFormatted(string $format = '%dmin, %dsec'): string
    {
        return sprintf($format, $this->getMinutes() ?: 1, $this->getSecondsOver());
    }

    /** @param  \Closure(int, int): string $closure The closure will receive the minutes and seconds as integers and should return a string. */
    public function formatUsingClosure(Closure $closure): string
    {
        return $closure($this->getMinutes(), $this->getSecondsOver());
    }

    protected function generate(): void
    {
        $wordCount = str_word_count($this->text);

        $minutes = $wordCount / static::$wordsPerMinute;
        $seconds = (int) floor($minutes * 60);

        $this->wordCount = $wordCount;
        $this->seconds = $seconds;
    }
}
