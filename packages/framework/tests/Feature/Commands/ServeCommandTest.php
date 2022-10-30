<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Console\Commands\ServeCommand
 */
class ServeCommandTest extends TestCase
{
    public function test_hyde_serve_command()
    {
        $this->artisan('serve')
            ->expectsOutput('Starting the HydeRC server... Press Ctrl+C to stop')
            ->assertExitCode(0);
    }

    public function test_hyde_serve_command_with_port_option()
    {
        $this->artisan('serve --port=8081')
            ->expectsOutput('Starting the HydeRC server... Press Ctrl+C to stop')
            ->assertExitCode(0);
    }

    public function test_hyde_serve_command_with_host_option()
    {
        $this->artisan('serve --host=foo')
            ->expectsOutput('Starting the HydeRC server... Press Ctrl+C to stop')
            ->assertExitCode(0);
    }

    public function test_hyde_serve_command_with_port_and_host_option()
    {
        $this->artisan('serve --port=8081 --host=foo')
            ->expectsOutput('Starting the HydeRC server... Press Ctrl+C to stop')
            ->assertExitCode(0);
    }

    public function test_hyde_serve_command_with_port_defined_in_config()
    {
        $this->app['config']->set('hyde.server.port', 8081);

        $this->artisan('serve')
            ->expectsOutput('Starting the HydeRC server... Press Ctrl+C to stop')
            ->assertExitCode(0);
    }

    public function test_hyde_serve_command_with_port_defined_in_config_and_port_option()
    {
        $this->app['config']->set('hyde.server.port', 8081);

        $this->artisan('serve --port=8082')
            ->expectsOutput('Starting the HydeRC server... Press Ctrl+C to stop')
            ->assertExitCode(0);
    }

    public function test_hyde_serve_command_with_port_missing_in_config_and_port_option()
    {
        $this->app['config']->set('hyde.server.port');

        $this->artisan('serve')
            ->expectsOutput('Starting the HydeRC server... Press Ctrl+C to stop')
            ->assertExitCode(0);
    }
}
