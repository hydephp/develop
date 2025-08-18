<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Mockery;
use Hyde\Testing\TestCase;
use Hyde\Foundation\PharSupport;
use Illuminate\Console\OutputStyle;
use Hyde\Console\Commands\DebugCommand;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Console\Commands\DebugCommand::class)]
class DebugCommandTest extends TestCase
{
    public function testDebugCommandCanRun()
    {
        $this->artisan('debug')->assertExitCode(0);
    }

    public function testItPrintsDebugInformation()
    {
        $this->artisan('debug')
            ->expectsOutput('HydePHP Debug Screen')
            ->expectsOutputToContain('Hyde Version:')
            ->expectsOutputToContain('Framework Version:')
            ->expectsOutputToContain('App Env:')
            ->expectsOutputToContain('Project directory:')
            ->expectsOutputToContain('Enabled features:')
            ->assertExitCode(0);
    }

    public function testItPrintsVerboseDebugInformation()
    {
        $this->artisan('debug --verbose')
            ->expectsOutput('HydePHP Debug Screen')
            ->expectsOutput('Project directory:')
            ->expectsOutput('Framework vendor path:')
            ->expectsOutputToContain('(vendor)')
            ->expectsOutputToContain('(real)')
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
            if (str_contains($message, 'Application binary path:')) {
                $wasCalled = true;
            }

            return true;
        });

        $command = new DebugCommand();
        $command->setOutput($output);
        $command->handle();

        $this->assertTrue($wasCalled, 'Expected "Application binary path" to be called');

        PharSupport::clearMocks();
    }
}
