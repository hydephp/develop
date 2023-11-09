<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Closure;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Process;
use TypeError;

/**
 * @covers \Hyde\Console\Commands\ServeCommand
 */
class ServeCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Process::fake();
    }

    public function test_hyde_serve_command()
    {
        $this->artisan('serve --no-ansi')
            ->expectsOutput('Starting the HydeRC server... Press Ctrl+C to stop')
            ->assertExitCode(0);

        Process::assertRan("php -S localhost:8080 {$this->binaryPath()}");
    }

    public function test_hyde_serve_command_with_port_option()
    {
        $this->artisan('serve --no-ansi --port=8081')
            ->expectsOutput('Starting the HydeRC server... Press Ctrl+C to stop')
            ->assertExitCode(0);

        Process::assertRan("php -S localhost:8081 {$this->binaryPath()}");
    }

    public function test_hyde_serve_command_with_host_option()
    {
        $this->artisan('serve --no-ansi --host=foo')
            ->expectsOutput('Starting the HydeRC server... Press Ctrl+C to stop')
            ->assertExitCode(0);

        Process::assertRan("php -S foo:8080 {$this->binaryPath()}");
    }

    public function test_hyde_serve_command_with_port_and_host_option()
    {
        $this->artisan('serve --no-ansi --port=8081 --host=foo')
            ->expectsOutput('Starting the HydeRC server... Press Ctrl+C to stop')
            ->assertExitCode(0);

        Process::assertRan("php -S foo:8081 {$this->binaryPath()}");
    }

    public function test_hyde_serve_command_with_port_defined_in_config()
    {
        config(['hyde.server.port' => 8081]);

        $this->artisan('serve --no-ansi')
            ->expectsOutput('Starting the HydeRC server... Press Ctrl+C to stop')
            ->assertExitCode(0);

        Process::assertRan("php -S localhost:8081 {$this->binaryPath()}");
    }

    public function test_hyde_serve_command_with_port_defined_in_config_and_port_option()
    {
        config(['hyde.server.port' => 8081]);

        $this->artisan('serve --no-ansi --port=8082')
            ->expectsOutput('Starting the HydeRC server... Press Ctrl+C to stop')
            ->assertExitCode(0);

        Process::assertRan("php -S localhost:8082 {$this->binaryPath()}");
    }

    public function test_hyde_serve_command_with_port_missing_in_config_and_port_option()
    {
        config(['hyde.server.port' => null]);

        $this->artisan('serve --no-ansi --port=8081')
            ->expectsOutput('Starting the HydeRC server... Press Ctrl+C to stop')
            ->assertExitCode(0);

        Process::assertRan("php -S localhost:8081 {$this->binaryPath()}");
    }

    public function test_hyde_serve_command_with_host_defined_in_config()
    {
        config(['hyde.server.host' => 'foo']);

        $this->artisan('serve --no-ansi')
            ->expectsOutput('Starting the HydeRC server... Press Ctrl+C to stop')
            ->assertExitCode(0);

        Process::assertRan("php -S foo:8080 {$this->binaryPath()}");
    }

    public function test_hyde_serve_command_with_host_defined_in_config_and_host_option()
    {
        config(['hyde.server.host' => 'foo']);

        $this->artisan('serve --no-ansi --host=bar')
            ->expectsOutput('Starting the HydeRC server... Press Ctrl+C to stop')
            ->assertExitCode(0);

        Process::assertRan("php -S bar:8080 {$this->binaryPath()}");
    }

    public function test_hyde_serve_command_with_host_missing_in_config_and_host_option()
    {
        config(['hyde.server.host' => null]);

        $this->artisan('serve --no-ansi --host=foo')
            ->expectsOutput('Starting the HydeRC server... Press Ctrl+C to stop')
            ->assertExitCode(0);

        Process::assertRan("php -S foo:8080 {$this->binaryPath()}");
    }

    public function test_hyde_serve_command_with_invalid_config_value()
    {
        $this->expectException(TypeError::class);
        config(['hyde.server.port' => 'foo']);

        $this->artisan('serve --no-ansi')
            ->expectsOutput('Starting the HydeRC server... Press Ctrl+C to stop')
            ->assertExitCode(0);
    }

    public function test_hyde_serve_command_passes_through_process_output()
    {
        Process::shouldReceive('forever')
            ->once()
            ->withNoArgs()
            ->andReturnSelf();

        Process::shouldReceive('run')
            ->once()
            ->withArgs(function (string $command, Closure $handle) {
                $handle('type', 'foo');

                return $command === "php -S localhost:8080 {$this->binaryPath()}";
            })
            ->andReturnSelf();

        $this->artisan('serve --no-ansi')
            ->expectsOutput('Starting the HydeRC server... Press Ctrl+C to stop')
            ->expectsOutput('foo')
            ->assertExitCode(0);
    }

    protected function binaryPath(): string
    {
        return Hyde::path('vendor/hyde/realtime-compiler/bin/server.php');
    }
}
