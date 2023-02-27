<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Closure;
use Hyde\Console\Concerns\Command;
use Hyde\Hyde;
use Hyde\Testing\UnitTestCase;
use Mockery;
use RuntimeException;
use Symfony\Component\Console\Style\OutputStyle;

/**
 * @covers \Hyde\Console\Concerns\Command
 */
class CommandTest extends UnitTestCase
{
    public static function setUpBeforeClass(): void
    {
        self::needsKernel();
    }

    public static function tearDownAfterClass(): void
    {
        if ($container = Mockery::getContainer()) {
            $container->mockery_close();
        }
    }

    public function test_create_clickable_filepath_creates_link_for_existing_file()
    {
        touch(Hyde::path('foo.txt'));

        $this->assertSame(
            sprintf('file://%s/foo.txt', str_replace('\\', '/', Hyde::path())),
            Command::createClickableFilepath('foo.txt')
        );

        unlink(Hyde::path('foo.txt'));
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

        $output = Mockery::mock(OutputStyle::class);
        $output->shouldReceive('writeln')->once()->withArgs(function (string $message): bool {
            return $this->assertIsSame('<info>foo </info>[<comment>bar</comment>]<info></info>', $message);
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

        $output = Mockery::mock(OutputStyle::class);
        $output->shouldReceive('writeln')->once()->withArgs(function (string $message): bool {
            return $this->assertIsSame('<info>foo </info>[<comment>bar</comment>]<info> baz</info>', $message);
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

        $output = Mockery::mock(OutputStyle::class);
        $output->shouldReceive('writeln')->once()->withArgs(function (string $message): bool {
            return $this->assertIsSame('<info>foo </info>[<comment>bar</comment>]<info> baz </info>[<comment>qux</comment>]<info></info>', $message);
        });

        $command->setMockedOutput($output);
        $command->handle();
    }

    public function testInlineGray()
    {
        $this->testOutput(function (Command $command) {
            $this->assertSame('<fg=gray>foo</>', $command->inlineGray('foo'));
        });
    }

    public function testGray()
    {
        $this->testOutput(closure: fn (Command $command) => $command->gray('foo'),
            expectations: fn ($output) => $output->shouldReceive('writeln')->once()->withArgs(
                fn (string $message) => $this->assertIsSame('<fg=gray>foo</>', $message)
            )
        );
    }

    public function testIndentedLine()
    {
        $this->testOutput(closure: fn (Command $command) => $command->indentedLine(2, 'foo'),
            expectations: fn ($output) => $output->shouldReceive('writeln')->once()->withArgs(
                fn ($message) => $this->assertIsSame('  foo', $message)
            )
        );
    }

    public function testIndentedLineWithMultipleIndentations()
    {
        $this->testOutput(closure: fn (Command $command) => $command->indentedLine(8, 'foo'),
            expectations: fn ($output) => $output->shouldReceive('writeln')->once()->withArgs(
                fn ($message) => $this->assertIsSame('        foo', $message)
            )
        );
    }

    public function testIndentedLineWithNoIndentation()
    {
        $this->testOutput(closure: fn (Command $command) => $command->indentedLine(0, 'foo'),
            expectations: fn ($output) => $output->shouldReceive('writeln')->once()->withArgs(
                fn ($message) => $this->assertIsSame('foo', $message)
            )
        );
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
        self::mockConfig();
        $command = new SafeThrowingTestCommand();
        $output = Mockery::mock(\Illuminate\Console\OutputStyle::class);
        $output->shouldReceive('writeln')->once()->withArgs(function (string $message) {
            $condition = str_starts_with($message, '<error>Error: This is a test at '.__FILE__.':');
            $this->assertTrue($condition);

            return $condition;
        });
        $command->setOutput($output);

        $code = $command->handle();

        $this->assertSame(1, $code);
    }

    public function testCanEnableThrowOnException()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('This is a test');

        self::mockConfig(['app.throw_on_console_exception' => true]);
        $command = new SafeThrowingTestCommand();
        $output = Mockery::mock(\Illuminate\Console\OutputStyle::class);
        $output->shouldReceive('writeln')->once();
        $command->setOutput($output);
        $code = $command->handle();

        $this->assertSame(1, $code);
    }

    protected function assertIsSame(string $expected, string $actual): bool
    {
        $this->assertSame($expected, $actual);

        return $actual === $expected;
    }

    protected function testOutput(Closure $closure, Closure $expectations = null): void
    {
        $command = new MockableTestCommand();
        $command->closure = $closure;

        $output = Mockery::mock(OutputStyle::class);

        if ($expectations) {
            tap($output, $expectations);
        }

        $command->setMockedOutput($output);
        $command->handle();
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
