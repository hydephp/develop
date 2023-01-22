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
    //
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
