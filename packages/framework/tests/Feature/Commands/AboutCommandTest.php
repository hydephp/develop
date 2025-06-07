<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Console\Commands\AboutCommand;
use Mockery;
use Hyde\Testing\TestCase;
use Hyde\Foundation\PharSupport;
use Illuminate\Console\OutputStyle;
use Hyde\Console\Commands\DebugCommand;
use Illuminate\Support\Composer;

/**
 * @covers \Hyde\Console\Commands\DebugCommand
 */
class AboutCommandTest extends TestCase
{
    public function testAboutCommandCanRun()
    {
        $this->artisan('about')->assertExitCode(0);
    }

    public function testItPrintsDebugInformation()
    {
        $this->artisan('about')
            // ->expectsOutput('HydePHP Debug Screen')
            ->expectsOutputToContain('Hyde Version')
            ->expectsOutputToContain('Framework Version')
            ->expectsOutputToContain('App Env')
            ->expectsOutputToContain('Project directory')
            ->expectsOutputToContain('Enabled features')
            ->assertExitCode(0);
    }

    public function testItPrintsVerboseDebugInformation()
    {
        $this->artisan('about --verbose')
            // ->expectsOutput('HydePHP Debug Screen')
            ->expectsOutputToContain('Project directory')
            ->expectsOutputToContain('Framework vendor path')
            ->expectsOutputToContain('vendor')
            ->expectsOutputToContain('real')
            ->assertExitCode(0);
    }

    public function testItPrintsPharDebugInformation()
    {
        PharSupport::mock('running', true);

        $wasCalled = false;

        $output = Mockery::mock(OutputStyle::class, [
            'writeln' => null,
            'newLine' => null,
            'isVerbose' => false,
        ])->makePartial();

        $output->shouldReceive('writeln')->withArgs(function ($message) use (&$wasCalled) {
            if (str_contains($message, 'Application binary path')) {
                $wasCalled = true;
            }

            return true;
        });

        $composer = app(Composer::class);
        $command = new AboutCommand($composer);
        $command->setOutput($output);
        $command->handle();

        $this->assertTrue($wasCalled, 'Expected "Application binary path" to be called');

        PharSupport::clearMocks();
    }
}
