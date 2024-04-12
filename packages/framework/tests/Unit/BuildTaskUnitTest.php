<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Mockery;
use Hyde\Testing\UnitTestCase;
use Illuminate\Console\OutputStyle;
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

    public function testItTracksExecutionTime()
    {
        $task = new InspectableTestBuildTask();

        $task->run();

        $this->assertTrue($task->isset('timeStart'));
        $this->assertGreaterThan(0, $task->property('timeStart'));
    }

    public function testItCanRunWithoutOutput()
    {
        $task = new InspectableTestBuildTask();

        $task->run();

        $this->assertFalse($task->isset('output'));
    }

    public function testItCanRunWithOutput()
    {
        $task = new InspectableTestBuildTask();

        $output = Mockery::mock(OutputStyle::class, [
            'write' => null,
            'writeln' => null,
        ]);

        $task->run($output);
        $this->assertTrue($task->isset('output'));
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
