<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Services;

use Hyde\Framework\Features\Publications\PublicationService;
use Hyde\Testing\TestCase;
use Illuminate\Console\View\Components\Component;
use LaravelZero\Framework\Commands\Command;

use Illuminate\Console\Application;
use Illuminate\Console\OutputStyle;
use Illuminate\Console\View\Components\Factory;
use Mockery as m;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * @covers \Hyde\Framework\Features\Publications\PublicationService
 */
class PublicationServiceTest extends TestCase
{
    public function testAskWithValidation()
    {
        $command = new class extends Command
        {
            public function handle()
            {
                //
            }
        };

        $application = m::mock(Application::class);
        $command->setLaravel($application);

        $input = new ArrayInput([]);
        $output = new NullOutput;
        $outputStyle = m::mock(OutputStyle::class);
        $application->shouldReceive('make')->with(OutputStyle::class, ['input' => $input, 'output' => $output])->andReturn($outputStyle);
        $application->shouldReceive('make')->with(Factory::class, ['output' => $outputStyle])->andReturn(m::mock(Factory::class));

        $application->shouldReceive('call')->with([$command, 'handle'])->andReturnUsing(function () use ($command, $application) {
            $commandCalled = m::mock(Command::class);

            $application->shouldReceive('make')->once()->with(Command::class)->andReturn($commandCalled);

            $commandCalled->shouldReceive('setApplication')->once()->with(null);
            $commandCalled->shouldReceive('setLaravel')->once()->with($application);
            $commandCalled->shouldReceive('run')->once();

            $command->call(Command::class);
        });

        $command->run($input, $output);
    }
}
