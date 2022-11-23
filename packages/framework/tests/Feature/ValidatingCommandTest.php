<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Console\Concerns\ValidatingCommand;
use Hyde\Testing\TestCase;
use Rgasch\Collection\Collection;

use Illuminate\Console\Application;
use Illuminate\Console\Command;
use Illuminate\Console\OutputStyle;
use Illuminate\Console\View\Components\Factory;
use Mockery;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * @covers \Hyde\Console\Concerns\ValidatingCommand
 */
class ValidatingCommandTest extends TestCase
{
    public function testAskWithValidation()
    {
        $command = new class extends ValidatingCommand {
            public function handle()
            {
                $name = $this->askWithValidation('name', 'What is your name?', ['required'], 'John Doe');
                $this->output->writeln("Hello $name!");
            }
        };

        $output = Mockery::mock(OutputStyle::class);

        $output->shouldReceive('ask')->once()->withArgs(function (string $question) {
            return $question === 'What is your name?';
        })->andReturn('John Doe');

        $output->shouldReceive('writeln')->once()->withArgs(function (string $message) {
            return $message === 'Hello John Doe!';
        });

        $command->setOutput($output);
        $command->handle();
    }
}
