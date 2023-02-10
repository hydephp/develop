<?php

declare(strict_types=1);

namespace Hyde\Support;

use Closure;
use Hyde\Facades\Filesystem;
use function floor;

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

    public function getWordCount(): int
    {
        return $this->wordCount;
    }

    public function getSeconds(): int
    {
        return (int) $this->getSecondsAsFloat();
    }

    public function getMinutes(): int
    {
        return (int) $this->getMinutesAsFloat();
    }

    public function getSecondsAsFloat(): float
    {
        return $this->seconds;
    }

    public function getMinutesAsFloat(): float
    {
        return $this->seconds / 60;
    }

    public function getFormatted(string $format = '%dmin, %dsec'): string
    {
        [$fMin, $fSec] = $this->getTokenized();

        return sprintf($format, $fMin, $fSec);
    }

    /** @param  \Closure(int, int): string $closure The closure will receive the minutes and seconds as integers and should return a string. */
    public function formatUsingClosure(Closure $closure): string
    {
        [$fMin, $fSec] = $this->getTokenized();

        return $closure($fMin, $fSec);
    }

    protected function generate(): void
    {
        $wordCount = str_word_count($this->text);

        $minutes = $wordCount / static::WORDS_PER_MINUTE;
        $seconds = $minutes * 60;

        $this->wordCount = $wordCount;
        $this->seconds = $seconds;
    }

    /** @return array<int, int> The minutes and seconds as integers. */
    protected function getTokenized(): array
    {
        $minutes = $this->getMinutesAsFloat();
        $fMin = (int) floor($minutes);
        $fSec = (int) floor(($minutes - $fMin) * 60);

        return [$fMin, $fSec];
    }
}
