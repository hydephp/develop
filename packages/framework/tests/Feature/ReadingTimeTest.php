<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Mockery;
use Hyde\Hyde;
use Hyde\Support\ReadingTime;
use Hyde\Testing\UnitTestCase;
use Illuminate\Filesystem\Filesystem;

/**
 * @covers \Hyde\Support\ReadingTime
 */
class ReadingTimeTest extends UnitTestCase
{
    public static function setUpBeforeClass(): void
    {
        self::setupKernel();
    }

    public function testConstruct()
    {
        $this->assertInstanceOf(ReadingTime::class, new ReadingTime('Hello world'));
    }

    public function testToString()
    {
        $this->assertSame('1min, 0sec', (string) new ReadingTime('Hello world'));
    }

    public function testGetWordCount()
    {
        $this->assertSame(0, (new ReadingTime($this->words(0)))->getWordCount());
        $this->assertSame(120, (new ReadingTime($this->words(120)))->getWordCount());
        $this->assertSame(240, (new ReadingTime($this->words(240)))->getWordCount());
        $this->assertSame(360, (new ReadingTime($this->words(360)))->getWordCount());
    }

    public function testGetMinutes()
    {
        $this->assertSame(0, (new ReadingTime($this->words(0)))->getMinutes());
        $this->assertSame(0, (new ReadingTime($this->words(120)))->getMinutes());
        $this->assertSame(1, (new ReadingTime($this->words(240)))->getMinutes());
        $this->assertSame(1, (new ReadingTime($this->words(360)))->getMinutes());
    }

    public function testGetSeconds()
    {
        $this->assertSame(0, (new ReadingTime($this->words(0)))->getSeconds());
        $this->assertSame(30, (new ReadingTime($this->words(120)))->getSeconds());
        $this->assertSame(60, (new ReadingTime($this->words(240)))->getSeconds());
        $this->assertSame(90, (new ReadingTime($this->words(360)))->getSeconds());
    }

    public function testGetSecondsOver()
    {
        $this->assertSame(0, (new ReadingTime($this->words(0)))->getSecondsOver());
        $this->assertSame(30, (new ReadingTime($this->words(120)))->getSecondsOver());
        $this->assertSame(0, (new ReadingTime($this->words(240)))->getSecondsOver());
        $this->assertSame(30, (new ReadingTime($this->words(360)))->getSecondsOver());
    }

    public function testGetFormatted()
    {
        $this->assertSame('1min, 0sec', (new ReadingTime($this->words(0)))->getFormatted());
        $this->assertSame('1min, 0sec', (new ReadingTime($this->words(120)))->getFormatted());
        $this->assertSame('1min, 0sec', (new ReadingTime($this->words(240)))->getFormatted());
        $this->assertSame('1min, 30sec', (new ReadingTime($this->words(360)))->getFormatted());
    }

    public function testGetFormattedWithCustomFormatting()
    {
        $this->assertSame('1:00', (new ReadingTime($this->words(0)))->getFormatted('%d:%02d'));
        $this->assertSame('1:00', (new ReadingTime($this->words(120)))->getFormatted('%d:%02d'));
        $this->assertSame('1:00', (new ReadingTime($this->words(240)))->getFormatted('%d:%02d'));
        $this->assertSame('1:30', (new ReadingTime($this->words(360)))->getFormatted('%d:%02d'));
    }

    public function testGetFormattedFormatsUpToOneMinuteWhenRoundUpIsSet()
    {
        $this->assertSame('1min, 0sec', (new ReadingTime($this->words(0)))->getFormatted());
        $this->assertSame('1min, 0sec', (new ReadingTime($this->words(120)))->getFormatted());
        $this->assertSame('1min, 0sec', (new ReadingTime($this->words(240)))->getFormatted());
        $this->assertSame('1min, 30sec', (new ReadingTime($this->words(360)))->getFormatted());
    }

    public function testFormatUsingClosure()
    {
        /**
         * @param  int  $minutes
         * @param  int  $seconds
         * @return string
         */
        $closure = function (int $minutes, int $seconds): string {
            return "$minutes minutes, $seconds seconds";
        };

        $this->assertSame('0 minutes, 0 seconds', (new ReadingTime($this->words(0)))->formatUsingClosure($closure));
        $this->assertSame('0 minutes, 30 seconds', (new ReadingTime($this->words(120)))->formatUsingClosure($closure));
        $this->assertSame('1 minutes, 0 seconds', (new ReadingTime($this->words(240)))->formatUsingClosure($closure));
        $this->assertSame('1 minutes, 30 seconds', (new ReadingTime($this->words(360)))->formatUsingClosure($closure));
    }

    public function testFromString()
    {
        $this->assertInstanceOf(ReadingTime::class, ReadingTime::fromString('Hello world'));
        $this->assertEquals(new ReadingTime('Hello world'), ReadingTime::fromString('Hello world'));
    }

    public function testFromFile()
    {
        app()->instance(Filesystem::class, Mockery::mock(Filesystem::class)->shouldReceive('get')->with(Hyde::path('foo.md'), false)->andReturn('Hello world')->getMock());

        $this->assertInstanceOf(ReadingTime::class, ReadingTime::fromFile('foo.md'));
        $this->assertEquals(new ReadingTime('Hello world'), ReadingTime::fromFile('foo.md'));

        Mockery::close();
        app()->forgetInstance(Filesystem::class);
    }

    protected function words(int $words): string
    {
        return implode(' ', array_fill(0, $words, 'word'));
    }
}
