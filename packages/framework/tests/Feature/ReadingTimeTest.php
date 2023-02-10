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
    public function test___construct()
    {
        $this->assertInstanceOf(ReadingTime::class, new ReadingTime('Hello world'));
    }

    public function test_getSeconds()
    {
        $this->assertSame(0, (new ReadingTime($this->words(0)))->getSeconds());
        $this->assertSame(30, (new ReadingTime($this->words(120)))->getSeconds());
        $this->assertSame(60, (new ReadingTime($this->words(240)))->getSeconds());
        $this->assertSame(90, (new ReadingTime($this->words(360)))->getSeconds());
    }

    public function test_getMinutes()
    {
        $this->assertSame(0, (new ReadingTime($this->words(0)))->getMinutes());
        $this->assertSame(0, (new ReadingTime($this->words(120)))->getMinutes());
        $this->assertSame(1, (new ReadingTime($this->words(240)))->getMinutes());
        $this->assertSame(1, (new ReadingTime($this->words(360)))->getMinutes());
    }

    public function test_getFormatted()
    {
        $this->assertSame('0min, 0sec', (new ReadingTime($this->words(0)))->getFormatted());
        $this->assertSame('0min, 30sec', (new ReadingTime($this->words(120)))->getFormatted());
        $this->assertSame('1min, 0sec', (new ReadingTime($this->words(240)))->getFormatted());
        $this->assertSame('1min, 30sec', (new ReadingTime($this->words(360)))->getFormatted());
    }

    public function test_getWordCount()
    {
        $this->assertSame(0, (new ReadingTime($this->words(0)))->getWordCount());
        $this->assertSame(120, (new ReadingTime($this->words(120)))->getWordCount());
        $this->assertSame(240, (new ReadingTime($this->words(240)))->getWordCount());
        $this->assertSame(360, (new ReadingTime($this->words(360)))->getWordCount());
    }

    protected function words(int $words): string
    {
        return implode(' ', array_fill(0, $words, 'word'));
    }
}
