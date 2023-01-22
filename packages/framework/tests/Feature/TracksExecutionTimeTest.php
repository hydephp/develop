<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Concerns\TracksExecutionTime;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Concerns\TracksExecutionTime
 */
class TracksExecutionTimeTest extends TestCase
{
    public function test_startClock()
    {
        $class = new TracksExecutionTimeTestClass();

        $this->assertFalse($class->isset('timeStart'));
        $class->startClock();

        $this->assertTrue($class->isset('timeStart'));
        $this->assertIsFloat($class->timeStart);
    }

    public function test_stopClock()
    {
        $class = new TracksExecutionTimeTestClass();
        $class->startClock();

        $this->assertIsFloat($class->stopClock());
    }

    public function test_getExecutionTimeString()
    {
        $class = new TracksExecutionTimeTestClass();
        $class->startClock();

        $this->assertIsString($class->getExecutionTimeString());
    }

    public function test_getExecutionTimeInMs()
    {
        $class = new TracksExecutionTimeTestClass();
        $class->startClock();

        $this->assertIsFloat($class->getExecutionTimeInMs());
    }
}

class TracksExecutionTimeTestClass {
    use TracksExecutionTime;

    public function __call(string $name, array $arguments)
    {
        return $this->$name(...$arguments);
    }

    public function __get(string $name)
    {
        return $this->$name;
    }

    public function isset(string $name): bool
    {
        return isset($this->$name);
    }
}
