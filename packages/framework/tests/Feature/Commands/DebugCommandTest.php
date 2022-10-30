<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Console\Commands\DebugCommand
 */
class DebugCommandTest extends TestCase
{
    public function test_debug_command_can_run()
    {
        $this->artisan('debug')->assertExitCode(0);
    }

    public function test_it_prints_debug_information()
    {
        $this->artisan('debug')
            ->expectsOutput('HydePHP Debug Screen')
            ->expectsOutputToContain('Git Version:')
            ->expectsOutputToContain('Hyde Version:')
            ->expectsOutputToContain('Framework Version:')
            ->expectsOutputToContain('App Env:')
            ->expectsOutputToContain('Project directory:')
            ->expectsOutputToContain('Enabled features:')
            ->assertExitCode(0);
    }

    public function test_it_prints_verbose_debug_information()
    {
        $this->artisan('debug --verbose')
            ->expectsOutput('HydePHP Debug Screen')
            ->expectsOutput('Project directory:')
            ->expectsOutput('Framework vendor path:')
            ->expectsOutputToContain('(vendor)')
            ->expectsOutputToContain('(real)')
            ->assertExitCode(0);
    }
}
