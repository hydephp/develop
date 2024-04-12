<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Testing\UnitTestCase;
use Hyde\Framework\Features\BuildTasks\BuildTask;

/**
 * @covers \Hyde\Framework\Features\BuildTasks\BuildTask
 */
class BuildTaskUnitTest extends UnitTestCase
{
    public function testCanCreateBuildTask()
    {
        $task = new EmptyTestBuildTask();

        $this->assertInstanceOf(BuildTask::class, $task);
    }
}

class EmptyTestBuildTask extends BuildTask
{
    public function handle(): void
    {
        // Do nothing
    }
}

class InspectableTestBuildTask extends BuildTask
{
    public function handle(): void
    {
        // Do nothing
    }

    public function isset(string $name): bool
    {
        return isset($this->{$name});
    }

    public function property(string $name): mixed
    {
        return $this->{$name};
    }

    public function call(string $name, mixed ...$args): mixed
    {
        return $this->{$name}(...$args);
    }
}
