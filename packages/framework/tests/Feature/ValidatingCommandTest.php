<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Console\Concerns\ValidatingCommand;
use Hyde\Testing\TestCase;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Facades\Validator;
use Mockery;
use RuntimeException;

/**
 * @covers \Hyde\Console\Concerns\ValidatingCommand
 */
class ValidatingCommandTest extends TestCase
{
    public function testAskWithValidationCapturesInput()
    {
        $command = $this->getCommand();

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
        $command = $this->getCommand();

        $output = Mockery::mock(OutputStyle::class);

        $output->shouldReceive('ask')->times(2)->withArgs(function (string $question) {
            return $question === 'What is your name?';
        })->andReturn('', 'Jane Doe');

        $output->shouldReceive('writeln')->times(1)->withArgs(function (string $message) {
            return $message === '<error>validation.required</error>';
        });

        $output->shouldReceive('writeln')->once()->withArgs(function (string $message) {
            return $message === 'Hello Jane Doe!';
        });

        $command->setOutput($output);
        $command->handle();
    }

    public function testAskWithValidationRetriesTooManyTimes()
    {
        $command = $this->getCommand();

        $output = Mockery::mock(OutputStyle::class);

        $output->shouldReceive('ask')->times(30)->withArgs(function (string $question) {
            return $question === 'What is your name?';
        })->andReturn('');

        $output->shouldReceive('writeln')->times(30)->withArgs(function (string $message) {
            return $message === '<error>validation.required</error>';
        });

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Too many validation errors trying to validate 'name' with rules: [required]");

        $command->setOutput($output);
        $command->handle();
    }

    public function testValidationIsCalled()
    {
        $command = $this->getCommand();

        $output = Mockery::mock(OutputStyle::class);

        $output->shouldReceive('ask')->once()->withArgs(function (string $question) {
            return $question === 'What is your name?';
        })->andReturn('Jane Doe');

        $output->shouldReceive('writeln')->once()->withArgs(function (string $message) {
            return $message === 'Hello Jane Doe!';
        });

        $validator = Validator::spy();
        $validator->shouldReceive('make')->once()->withArgs(function (array $data) {
            return $data === ['name' => 'Jane Doe'];
        })->andReturn($validator);
        $validator->shouldReceive('passes')->once()->andReturn(true);

        $command->setOutput($output);
        $command->handle();
    }

    protected function getCommand()
    {
        return new class extends ValidatingCommand {
            public function handle()
            {
                $name = $this->askWithValidation('name', 'What is your name?', ['required'], 'John Doe');
                $this->output->writeln("Hello $name!");
            }
        };
    }
}
