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

    public function testItPrintsStartMessage()
    {
        $task = tap(new BufferedTestBuildTask(), fn ($task) => $task->run());

        $this->assertStringContainsString('Running generic build task', $task->buffer[0]);
    }

    public function testItPrintsFinishMessage()
    {
        $task = tap(new BufferedTestBuildTask(), fn ($task) => $task->run());

        $this->assertStringContainsString('Done in', $task->buffer[1]);
        $this->assertStringContainsString('ms', $task->buffer[1]);
    }

    public function testRunMethodHandlesTask()
    {
        $task = new InspectableTestBuildTask();

        $this->assertFalse($task->property('wasHandled'));

        $task->run();

        $this->assertTrue($task->property('wasHandled'));
    }

    public function testRunMethodReturnsExitCode()
    {
        $task = new InspectableTestBuildTask();

        $this->assertSame(0, $task->run());

        $task->set('exitCode', 1);

        $this->assertSame(1, $task->run());
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
    protected bool $wasHandled = false;

    public function handle(): void
    {
        $this->wasHandled = true;
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

    public function set(string $name, mixed $value): void
    {
        $this->{$name} = $value;
    }
}

class BufferedTestBuildTask extends InspectableTestBuildTask
{
    public array $buffer = [];

    public function write(string $message): void
    {
        $this->buffer[] = $message;
    }

    public function writeln(string $message): void
    {
        $this->buffer[] = $message;
    }
}
