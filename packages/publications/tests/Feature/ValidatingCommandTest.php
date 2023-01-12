<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

use Closure;
use Hyde\Publications\Commands\ValidatingCommand;
use Hyde\Testing\TestCase;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Facades\Validator;
use Mockery;
use PHPUnit\Framework\ExpectationFailedException;
use RuntimeException;
use function str_starts_with;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * @covers \Hyde\Publications\Commands\ValidatingCommand
 */
class ValidatingCommandTest extends TestCase
{
    public function testSafeHandle()
    {
        $this->assertSame(0, (new SafeValidatingTestCommand())->handle());
    }

    public function testSafeHandleException()
    {
        $command = new SafeThrowingValidatingTestCommand();
        $output = Mockery::mock(OutputStyle::class);
        $output->shouldReceive('writeln')->once()->withArgs(function (string $message) {
            return str_starts_with($message, '<error>Error: This is a test at '.__FILE__.':');
        });
        $command->setOutput($output);

        $code = $command->handle();

        $this->assertSame(1, $code);
    }

    public function testAskWithValidationCapturesInput()
    {
        $command = new ValidationTestCommand();
        $output = Mockery::mock(OutputStyle::class);

        $output->shouldReceive('ask')->once()->withArgs(function (string $question) {
            return $question === 'What is your name?';
        })->andReturn('Jane Doe');

        $output->shouldReceive('writeln')->once()->withArgs(function (string $message) {
            return $message === 'Hello Jane Doe!';
        });

        $command->setOutput($output);
        $command->handle();
    }

    public function testAskWithValidationRetries()
    {
        $command = new ValidationTestCommand();
        $output = Mockery::mock(OutputStyle::class);

        $output->shouldReceive('ask')->times(2)->withArgs(function (string $question) {
            return $question === 'What is your name?';
        })->andReturn('', 'Jane Doe');

        $output->shouldReceive('writeln')->times(1)->withArgs(function (string $message) {
            return $message === '<error>The name field is required.</error>';
        });

        $output->shouldReceive('writeln')->once()->withArgs(function (string $message) {
            return $message === 'Hello Jane Doe!';
        });

        $command->setOutput($output);
        $command->handle();
    }

    public function testAskWithValidationRetriesTooManyTimes()
    {
        $command = new ValidationTestCommand();
        $output = Mockery::mock(OutputStyle::class);

        $output->shouldReceive('ask')->times(30)->withArgs(function (string $question) {
            return $question === 'What is your name?';
        })->andReturn('');

        $output->shouldReceive('writeln')->times(30)->withArgs(function (string $message) {
            return $message === '<error>The name field is required.</error>';
        });

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Too many validation errors trying to validate 'name' with rules: [required]");

        $command->setOutput($output);
        $command->handle();

        $output->shouldNotReceive('writeln')->once()->withArgs(function (string $message) {
            return str_starts_with($message, 'Hello');
        });
    }

    public function testValidationIsCalled()
    {
        $command = new ValidationTestCommand();
        $output = Mockery::mock(OutputStyle::class);

        $output->shouldReceive('ask')->once()->andReturn('Jane Doe');
        $output->shouldReceive('writeln')->once();

        $validator = Validator::spy();
        $validator->shouldReceive('make')->once()->withArgs(function (array $data, array $rules) {
            return $data === ['name' => 'Jane Doe']
                && $rules === ['name' => ['required']];
        })->andReturn($validator);
        $validator->shouldReceive('passes')->once()->andReturn(true);

        $command->setOutput($output);
        $command->handle();
    }

    public function testReloadableChoiceHelper()
    {
        $command = new ReloadableChoiceTestCommand();
        $output = Mockery::mock(OutputStyle::class);

        $output->shouldReceive('askQuestion')->once()->withArgs(function (ChoiceQuestion $question) {
            return $this->assertEqualsAsBoolean(new ChoiceQuestion('Select an option', [
                '<fg=bright-blue>[Reload options]</>',
                'foo',
                'bar',
                'baz',
            ], null), $question);
        })->andReturn('foo');

        $output->shouldReceive('writeln')->once()->withArgs(function (string $message) {
            return $message === 'You selected foo';
        });

        $command->setOutput($output);
        $command->handle();
    }

    public function testReloadableChoiceHelperSelectingReload()
    {
        $command = new ReloadableChoiceTestCommand();
        $output = Mockery::mock(OutputStyle::class);

        $output->shouldReceive('askQuestion')->once()->withArgs(function (ChoiceQuestion $question) {
            return $this->assertEqualsAsBoolean(new ChoiceQuestion('Select an option', [
                '<fg=bright-blue>[Reload options]</>',
                'foo',
                'bar',
                'baz',
            ], null), $question);
        })->andReturn('<fg=bright-blue>[Reload options]</>');

        $output->shouldReceive('askQuestion')->once()->withArgs(function (ChoiceQuestion $question) {
            return $this->assertEqualsAsBoolean(new ChoiceQuestion('Select an option', [
                '<fg=bright-blue>[Reload options]</>',
                'bar',
                'baz',
                'qux',
            ], null), $question);
        })->andReturn('qux');

        $output->shouldReceive('writeln')->once()->withArgs(function (string $message) {
            return $message === 'You selected qux';
        });

        $command->setOutput($output);
        $command->handle();
    }

    public function testHandleException()
    {
        $command = new ThrowingValidatingTestCommand();
        $output = Mockery::mock(OutputStyle::class);

        $output->shouldReceive('writeln')->once()->withArgs(function (string $message) {
            return $message === '<error>Error: This is a test</error>';
        });

        $command->setOutput($output);
        $code = $command->handle();

        $this->assertSame(1, $code);
    }

    public function testHandleExceptionWithErrorLocation()
    {
        $command = new ThrowingValidatingTestCommand();
        $output = Mockery::mock(OutputStyle::class);

        $output->shouldReceive('writeln')->once()->withArgs(function (string $message) {
            return $message === '<error>Error: This is a test at TestClass.php:10</error>';
        });

        $command->setOutput($output);
        $code = $command->handle('TestClass.php', 10);

        $this->assertSame(1, $code);
    }

    public function testCanEnableThrowOnException()
    {
        config(['app.throw_on_console_exception' => true]);
        $command = new ThrowingValidatingTestCommand();

        $output = Mockery::mock(OutputStyle::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('This is a test');

        $command->setOutput($output);
        $code = $command->handle();

        $this->assertSame(1, $code);
    }

    public function testInfoComment()
    {
        $command = new DynamicValidatingTestCommand();
        $command->closure = function (ValidatingCommand $command) {
            $command->infoComment('foo', 'bar');
        };
        $output = Mockery::mock(OutputStyle::class);

        $output->shouldReceive('writeln')->once()->withArgs(function (string $message) {
            return $message === '<info>foo</info> [<comment>bar</comment>]';
        });

        $command->setOutput($output);
        $command->handle();
    }

    public function testInfoCommentWithExtraInfo()
    {
        $command = new DynamicValidatingTestCommand();
        $command->closure = function (ValidatingCommand $command) {
            $command->infoComment('foo', 'bar', 'baz');
        };
        $output = Mockery::mock(OutputStyle::class);

        $output->shouldReceive('writeln')->once()->withArgs(function (string $message) {
            return $message === '<info>foo</info> [<comment>bar</comment>] <info>baz</info>';
        });

        $command->setOutput($output);
        $command->handle();
    }

    protected function assertEqualsAsBoolean($expected, $question): bool
    {
        try {
            $this->assertEquals($expected, $question);
        } catch (ExpectationFailedException) {
            return false;
        }

        return true;
    }
}

class SafeValidatingTestCommand extends ValidatingCommand
{
    //
}

class SafeThrowingValidatingTestCommand extends ValidatingCommand
{
    public function safeHandle(): int
    {
        throw new RuntimeException('This is a test');
    }
}

class ValidationTestCommand extends ValidatingCommand
{
    public function handle(): int
    {
        $name = $this->askWithValidation('name', 'What is your name?', ['required'], 'John Doe');
        $this->output->writeln("Hello $name!");

        return 0;
    }
}

class ThrowingValidatingTestCommand extends ValidatingCommand
{
    public function handle(?string $file = 'TestCommand.php', ?int $line = null): int
    {
        try {
            throw new RuntimeException('This is a test');
        } catch (RuntimeException $exception) {
            return $this->handleException($exception, $file, $line);
        }
    }
}

class ReloadableChoiceTestCommand extends ValidatingCommand
{
    protected bool $isFirstRun = true;

    public function handle(): int
    {
        $selection = $this->reloadableChoice(function () {
            if ($this->isFirstRun) {
                $this->isFirstRun = false;

                return ['foo', 'bar', 'baz'];
            }

            return ['bar', 'baz', 'qux'];
        }, 'Select an option');

        $this->output->writeln("You selected $selection");

        return 0;
    }
}

class DynamicValidatingTestCommand extends ValidatingCommand
{
    public Closure $closure;

    public function handle(): int
    {
        ($this->closure)($this);

        return 0;
    }
}
