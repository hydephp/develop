<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature;

use Hyde\Console\Concerns\ValidatingCommand;
use Hyde\Testing\TestCase;
use Illuminate\Console\OutputStyle;
use Mockery;

/**
 * @covers \Hyde\Console\Concerns\ValidatingCommand
 */
class ValidatingCommandTest extends TestCase
{
    public function testAskWithValidation()
    {
        $command = new class extends ValidatingCommand
        {
            public function handle()
            {
                $name = $this->askWithValidation('name', 'What is your name?', ['required'], 'John Doe');
                $this->output->writeln("Hello $name!");
            }
        };

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
}
