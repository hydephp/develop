<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Closure;
use Hyde\Console\Concerns\Command;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Mockery;
use RuntimeException;
use Symfony\Component\Console\Style\OutputStyle;

/**
 * @covers \Hyde\Console\Concerns\Command
 */
class CommandTest extends TestCase
{
    public function test_create_clickable_filepath_creates_link_for_existing_file()
    {
        $this->file('foo.txt');

        $this->assertSame(
            sprintf('file://%s/foo.txt', str_replace('\\', '/', Hyde::path())),
            Command::createClickableFilepath('foo.txt')
        );
    }

    public function test_create_clickable_filepath_creates_link_for_non_existing_file()
    {
        $this->assertSame(
            sprintf('file://%s/foo.txt', str_replace('\\', '/', Hyde::path())),
            Command::createClickableFilepath('foo.txt')
        );
    }

    public function testInfoComment()
    {
        $command = new MockableTestCommand();
        $command->closure = function (Command $command) {
            $command->infoComment('foo [bar]');
        };

        $output = $this->mock(OutputStyle::class);
        $output->shouldReceive('writeln')->once()->withArgs(function (string $message): bool {
            return $message === '<info>foo </info>[<comment>bar</comment>]<info></info>';
        });

        $command->setMockedOutput($output);
        $command->handle();
    }

    public function testInfoCommentWithExtraInfo()
    {
        $command = new MockableTestCommand();
        $command->closure = function (Command $command) {
            $command->infoComment('foo [bar] baz');
        };

        $output = $this->mock(OutputStyle::class);
        $output->shouldReceive('writeln')->once()->withArgs(function (string $message): bool {
            return $message === '<info>foo </info>[<comment>bar</comment>]<info> baz</info>';
        });

        $command->setMockedOutput($output);
        $command->handle();
    }

    public function testInfoCommentWithExtraInfoAndComments()
    {
        $command = new MockableTestCommand();
        $command->closure = function (Command $command) {
            $command->infoComment('foo [bar] baz [qux]');
        };

        $output = $this->mock(OutputStyle::class);
        $output->shouldReceive('writeln')->once()->withArgs(function (string $message): bool {
            return $message === '<info>foo </info>[<comment>bar</comment>]<info> baz </info>[<comment>qux</comment>]<info></info>';
        });

        $command->setMockedOutput($output);
        $command->handle();
    }

    public function testGray()
    {
        $command = new MockableTestCommand();
        $command->closure = function (Command $command) {
            $command->gray('foo');
        };

        $output = $this->mock(OutputStyle::class);
        $output->shouldReceive('writeln')->once()->withArgs(function (string $message): bool {
            return $message === '<fg=gray>foo</>';
        });

        $command->setMockedOutput($output);
        $command->handle();
    }

    public function testInlineGray()
    {
        $command = new MockableTestCommand();
        $command->closure = function (Command $command) {
            $this->assertSame('<fg=gray>foo</>', $command->inlineGray('foo'));
        };

        $command->handle();
    }

    public function testIndentedLine()
    {
        $command = new MockableTestCommand();
        $command->closure = function (Command $command) {
            $command->indentedLine(2, 'foo');
        };

        $output = $this->mock(OutputStyle::class);
        $output->shouldReceive('writeln')->once()->withArgs(function (string $message): bool {
            return $message === '  foo';
        });

        $command->setMockedOutput($output);
        $command->handle();
    }

    public function testHandleCallsBaseSafeHandle()
    {
        $this->assertSame(0, (new TestCommand())->handle());
    }

    public function testHandleCallsChildSafeHandle()
    {
        $this->assertSame(1, (new SafeHandleTestCommand())->handle());
    }

    public function testSafeHandleException()
    {
        $command = new SafeThrowingTestCommand();
        $output = Mockery::mock(\Illuminate\Console\OutputStyle::class);
        $output->shouldReceive('writeln')->once()->withArgs(function (string $message) {
            return str_starts_with($message, '<error>Error: This is a test at '.__FILE__.':');
        });
        $command->setOutput($output);

        $code = $command->handle();

        $this->assertSame(1, $code);
    }

    public function testCanEnableThrowOnException()
    {
        $this->throwOnConsoleException();
        $command = new SafeThrowingTestCommand();

        $output = Mockery::mock(\Illuminate\Console\OutputStyle::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('This is a test');

        $command->setOutput($output);
        $code = $command->handle();

        $this->assertSame(1, $code);
    }
}

class MockableTestCommand extends Command
{
    public Closure $closure;

    public function handle(): int
    {
        ($this->closure)($this);

        return 0;
    }

    public function setMockedOutput($output)
    {
        $this->output = $output;
    }
}

class TestCommand extends Command
{
    //
}

class SafeHandleTestCommand extends Command
{
    public function safeHandle(): int
    {
        return 1;
    }
}

class SafeThrowingTestCommand extends Command
{
    public function safeHandle(): int
    {
        throw new RuntimeException('This is a test');
    }
}
