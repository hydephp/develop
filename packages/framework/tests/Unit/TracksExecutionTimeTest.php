<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Concerns\TracksExecutionTime;
use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Framework\Concerns\TracksExecutionTime
 */
class TracksExecutionTimeTest extends UnitTestCase
{
    public function test_startClock()
    {
        $class = new TracksExecutionTimeTestClass();

        $this->assertFalse($class->isset('timeStart'));
        $class->startClock();

        $this->assertTrue($class->isset('timeStart'));
        $this->assertIsFloat($class->timeStart);
        // Assert that the difference between the two is less than 1 second to account for time drift (causes 1/10 000 tests to fail)
        $this->assertLessThan(1, abs(microtime(true) - $class->timeStart));
    }

    public function test_stopClock()
    {
        $class = new TracksExecutionTimeTestClass();
        $class->startClock();

        $this->assertIsFloat($class->stopClock());
        $this->assertLessThan(1, $class->stopClock());
    }

    public function test_getExecutionTimeInMs()
    {
        $class = new TracksExecutionTimeTestClass();
        $class->startClock();

        $this->assertIsFloat($class->getExecutionTimeInMs());
        $this->assertLessThan(1, $class->getExecutionTimeInMs());
    }

    public function test_getExecutionTimeString()
    {
        $class = new TracksExecutionTimeTestClass();
        $class->startClock();

        $this->assertIsString($class->getExecutionTimeString());
        $this->assertTrue(str_starts_with($class->getExecutionTimeString(), '0'));
        $this->assertTrue(str_ends_with($class->getExecutionTimeString(), 'ms'));
    }
}

class TracksExecutionTimeTestClass
{
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
