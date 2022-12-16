<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Console\Commands\Helpers\InputStreamHandler;
use Hyde\Testing\TestCase;
use Illuminate\Console\Command;
use Illuminate\Console\OutputStyle;
use Mockery;

use function implode;

/**
 * @covers \Hyde\Console\Commands\Helpers\InputStreamHandler
 */
class InputStreamHandlerTest extends TestCase
{
    public function testCanCollectInput()
    {
        InputStreamHandler::mockInput('foo');

        $this->assertSame(0, $this->makeCommand('foo')->handle());
    }

    public function testCanCollectMultipleInputLines()
    {
        InputStreamHandler::mockInput("foo\nbar\nbaz\n");

        $this->assertSame(0, $this->makeCommand('foo, bar, baz')->handle());
    }

    protected function makeCommand(string $expected): TestCommand
    {
        $command = new TestCommand;
        $output  = Mockery::mock(OutputStyle::class);
        $output->shouldReceive('writeln')->once()->withArgs(function (string $message) use ($expected) {
            return $message === $expected;
        });
        $command->setOutput($output);
        return $command;
    }
}

class TestCommand extends Command {
    public function handle(): int
    {
        $this->output->writeln(implode(', ', InputStreamHandler::call()));

        return 0;
    }
}
