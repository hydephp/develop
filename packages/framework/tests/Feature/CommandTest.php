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

    public function testUserExitConstant()
    {
        $this->assertSame(130, Command::USER_EXIT);
    }

    public function testFileLinkHelperCreatesLinkForExistingFile()
    {
        touch(Hyde::path('foo.txt'));

        $this->assertSame(
            sprintf('file://%s/foo.txt', str_replace('\\', '/', Hyde::path())),
            Command::fileLink('foo.txt')
        );

        unlink(Hyde::path('foo.txt'));
    }

    public function testFileLinkHelperCreatesLinkForNonExistingFile()
    {
        $this->assertSame(
            sprintf('file://%s/foo.txt', str_replace('\\', '/', Hyde::path())),
            Command::fileLink('foo.txt')
        );
    }

    public function testFileLinkHelperWithCustomLabel()
    {
        $this->assertSame(
            sprintf('<href=file://%s/foo.txt>bar</>', str_replace('\\', '/', Hyde::path())),
            Command::fileLink('foo.txt', 'bar')
        );
    }

    public function testFileLinkHelperWithAbsolutePathInput()
    {
        $this->assertSame(
            sprintf('file://%s/foo.txt', str_replace('\\', '/', Hyde::path())),
            Command::fileLink(Hyde::path('foo.txt'))
        );
    }

    public function testFileLinkHelperWithAbsolutePathInputAndCustomLabel()
    {
        $this->assertSame(
            sprintf('<href=file://%s/foo.txt>bar</>', str_replace('\\', '/', Hyde::path())),
            Command::fileLink(Hyde::path('foo.txt'), 'bar')
        );
    }

    public function testFileLinkHelperWithAbsolutePathAndRealFile()
    {
        touch(Hyde::path('foo.txt'));

        $this->assertSame(
            sprintf('file://%s/foo.txt', str_replace('\\', '/', Hyde::path())),
            Command::fileLink(Hyde::path('foo.txt'))
        );

        unlink(Hyde::path('foo.txt'));
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

    public function testHref()
    {
        $this->testOutput(function ($command) {
            $this->assertSame('<href=link>label</>', $command->href('link', 'label'));
        });
    }

    public function testInlineGray()
    {
        $this->testOutput(function ($command) {
            $this->assertSame('<fg=gray>foo</>', $command->inlineGray('foo'));
        });
    }

    public function testGray()
    {
        $this->testOutputReceivesLine(fn (Command $command) => $command->gray('foo'), '<fg=gray>foo</>');
    }

    public function testIndentedLine()
    {
        $this->testOutputReceivesLine(fn (Command $command) => $command->indentedLine(2, 'foo'), '  foo');
    }

    public function testIndentedLineWithMultipleIndentations()
    {
        $this->testOutputReceivesLine(fn (Command $command) => $command->indentedLine(8, 'foo'), '        foo');
    }

    public function testIndentedLineWithNoIndentation()
    {
        $this->testOutputReceivesLine(fn (Command $command) => $command->indentedLine(0, 'foo'), 'foo');
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

    public function testAskForString()
    {
        $this->testOutput(function ($command) {
            $this->assertSame('foo', $command->askForString('foo'));
        }, function ($output) {
            $output->shouldReceive('ask')->once()->withArgs(function (string $question, ?string $default): bool {
                return $this->assertIsSame('foo', $question) && $this->assertIsNull($default);
            })->andReturn('foo');
        });
    }

    public function testAskForStringWithDefaultValue()
    {
        $this->testOutput(function ($command) {
            $this->assertSame('foo', $command->askForString('foo', 'bar'));
        }, function ($output) {
            $output->shouldReceive('ask')->once()->withArgs(function (string $question, ?string $default): bool {
                return $this->assertIsSame('foo', $question) && $this->assertIsSame('bar', $default);
            })->andReturn('foo');
        });
    }

    public function testAskForStringWithDefaultValueSupplyingNull()
    {
        $this->testOutput(function ($command) {
            $this->assertSame('bar', $command->askForString('foo', 'bar'));
        }, function ($output) {
            $output->shouldReceive('ask')->once()->withArgs(function (string $question, ?string $default): bool {
                return $this->assertIsSame('foo', $question) && $this->assertIsSame('bar', $default);
            })->andReturn(null);
        });
    }

    protected function assertIsSame(string $expected, string $actual): bool
    {
        $this->assertSame($expected, $actual);

        return $actual === $expected;
    }

    protected function assertIsNull(mixed $expected): bool
    {
        $this->assertNull($expected);

        return $expected === null;
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

    protected function testOutputReceivesLine(Closure $closure, string $expected): void
    {
        $command = new MockableTestCommand();
        $command->closure = $closure;

        $output = Mockery::mock(OutputStyle::class);

        tap($output, fn ($output) => $output->shouldReceive('writeln')->once()->withArgs(
            fn ($message) => $this->assertIsSame($expected, $message))
        );

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
