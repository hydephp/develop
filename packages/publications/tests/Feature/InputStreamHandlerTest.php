<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

use Hyde\Publications\Commands\Helpers\InputStreamHandler;
use Hyde\Testing\TestCase;
use Illuminate\Console\Command;
use Illuminate\Console\OutputStyle;
use Mockery;

/**
 * @covers \Hyde\Publications\Commands\Helpers\InputStreamHandler
 */
class InputStreamHandlerTest extends TestCase
{
    public function testCanCollectInput()
    {
        InputStreamHandler::mockInput("foo\n<<<");

        $this->assertSame(0, $this->makeCommand(['foo'])->handle());
    }

    public function testCanTerminateWithHereSequence()
    {
        InputStreamHandler::mockInput("foo\nbar\nbaz\n<<<");

        $this->assertSame(0, $this->makeCommand(['foo', 'bar', 'baz'])->handle());
    }

    public function testCanTerminateWithHereSequenceAfterCarriageReturns()
    {
        InputStreamHandler::mockInput("foo\r\nbar\r\nbaz\r\n<<<");

        $this->assertSame(0, $this->makeCommand(['foo', 'bar', 'baz'])->handle());
    }

    public function testCanTerminateWithEndOfTransmissionSequence()
    {
        InputStreamHandler::mockInput("foo\nbar\nbaz\n\x04");

        $this->assertSame(0, $this->makeCommand(['foo', 'bar', 'baz'])->handle());
    }

    public function testCanCollectMultipleInputLines()
    {
        InputStreamHandler::mockInput("foo\nbar\nbaz\n<<<");

        $this->assertSame(0, $this->makeCommand(['foo', 'bar', 'baz'])->handle());
    }

    public function testCanEnterMultipleCarriageReturns()
    {
        InputStreamHandler::mockInput("foo\r\nbar\r\nbaz\r\n<<<");

        $this->assertSame(0, $this->makeCommand(['foo', 'bar', 'baz'])->handle());
    }

    public function testCanEnterMultipleUnixEndings()
    {
        InputStreamHandler::mockInput("foo\nbar\nbaz\n<<<");

        $this->assertSame(0, $this->makeCommand(['foo', 'bar', 'baz'])->handle());
    }

    public function testTerminationMessage()
    {
        $message = 'Terminate with <comment><<<</comment> or press <comment>Ctrl+D</comment>';
        if (PHP_OS_FAMILY === 'Windows') {
            $message .= ' then <comment>Enter</comment>';
        }
        $expected = "$message to finish";

        $this->assertSame($expected, InputStreamHandler::terminationMessage());
    }

    public function testTerminationSequenceConstant()
    {
        $this->assertSame('<<<', InputStreamHandler::TERMINATION_SEQUENCE);
    }

    public function testEndOfTransmissionConstant()
    {
        $this->assertSame("\x04", InputStreamHandler::END_OF_TRANSMISSION);
    }

    protected function makeCommand(array $expected): TestCommand
    {
        $command = new TestCommand;
        $output = Mockery::mock(OutputStyle::class);
        $output->shouldReceive('writeln')->once()->withArgs(function (string $message) use ($expected) {
            return $message === json_encode($expected);
        });
        $command->setOutput($output);

        return $command;
    }
}

class TestCommand extends Command
{
    public function handle(): int
    {
        $this->output->writeln(json_encode(InputStreamHandler::call()));

        return 0;
    }
}
