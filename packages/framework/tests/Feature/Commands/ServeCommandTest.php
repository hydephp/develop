<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Closure;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Contracts\Process\InvokedProcess;
use Illuminate\Support\Facades\Process;
use TypeError;

/**
 * @see \Hyde\Framework\Testing\Unit\ServeCommandOptionsUnitTest
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\RealtimeCompiler\Console\Commands\ServeCommand::class)]
class ServeCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Process::fake();
    }

    public function testHydeServeCommand()
    {
        $this->artisan('serve --no-ansi')
            ->expectsOutput('Starting the HydeRC server... Use Ctrl+C to stop')
            ->assertExitCode(0);

        Process::assertRan("php -S localhost:8080 {$this->binaryPath()}");
    }

    public function testHydeServeCommandWithPortOption()
    {
        $this->artisan('serve --no-ansi --port=8081')
            ->expectsOutput('Starting the HydeRC server... Use Ctrl+C to stop')
            ->assertExitCode(0);

        Process::assertRan("php -S localhost:8081 {$this->binaryPath()}");
    }

    public function testHydeServeCommandWithHostOption()
    {
        $this->artisan('serve --no-ansi --host=foo')
            ->expectsOutput('Starting the HydeRC server... Use Ctrl+C to stop')
            ->assertExitCode(0);

        Process::assertRan("php -S foo:8080 {$this->binaryPath()}");
    }

    public function testHydeServeCommandWithPortAndHostOption()
    {
        $this->artisan('serve --no-ansi --port=8081 --host=foo')
            ->expectsOutput('Starting the HydeRC server... Use Ctrl+C to stop')
            ->assertExitCode(0);

        Process::assertRan("php -S foo:8081 {$this->binaryPath()}");
    }

    public function testHydeServeCommandWithPortDefinedInConfig()
    {
        config(['hyde.server.port' => 8081]);

        $this->artisan('serve --no-ansi')
            ->expectsOutput('Starting the HydeRC server... Use Ctrl+C to stop')
            ->assertExitCode(0);

        Process::assertRan("php -S localhost:8081 {$this->binaryPath()}");
    }

    public function testHydeServeCommandWithPortDefinedInConfigAndPortOption()
    {
        config(['hyde.server.port' => 8081]);

        $this->artisan('serve --no-ansi --port=8082')
            ->expectsOutput('Starting the HydeRC server... Use Ctrl+C to stop')
            ->assertExitCode(0);

        Process::assertRan("php -S localhost:8082 {$this->binaryPath()}");
    }

    public function testHydeServeCommandWithPortMissingInConfigAndPortOption()
    {
        config(['hyde.server.port' => null]);

        $this->artisan('serve --no-ansi --port=8081')
            ->expectsOutput('Starting the HydeRC server... Use Ctrl+C to stop')
            ->assertExitCode(0);

        Process::assertRan("php -S localhost:8081 {$this->binaryPath()}");
    }

    public function testHydeServeCommandWithHostDefinedInConfig()
    {
        config(['hyde.server.host' => 'foo']);

        $this->artisan('serve --no-ansi')
            ->expectsOutput('Starting the HydeRC server... Use Ctrl+C to stop')
            ->assertExitCode(0);

        Process::assertRan("php -S foo:8080 {$this->binaryPath()}");
    }

    public function testHydeServeCommandWithHostDefinedInConfigAndHostOption()
    {
        config(['hyde.server.host' => 'foo']);

        $this->artisan('serve --no-ansi --host=bar')
            ->expectsOutput('Starting the HydeRC server... Use Ctrl+C to stop')
            ->assertExitCode(0);

        Process::assertRan("php -S bar:8080 {$this->binaryPath()}");
    }

    public function testHydeServeCommandWithHostMissingInConfigAndHostOption()
    {
        config(['hyde.server.host' => null]);

        $this->artisan('serve --no-ansi --host=foo')
            ->expectsOutput('Starting the HydeRC server... Use Ctrl+C to stop')
            ->assertExitCode(0);

        Process::assertRan("php -S foo:8080 {$this->binaryPath()}");
    }

    public function testHydeServeCommandWithInvalidConfigValue()
    {
        $this->expectException(TypeError::class);
        config(['hyde.server.port' => 'foo']);

        $this->artisan('serve --no-ansi')
            ->expectsOutput('Starting the HydeRC server... Use Ctrl+C to stop')
            ->assertExitCode(0);
    }

    public function testHydeServeCommandPassesThroughProcessOutput()
    {
        $mockProcess = mock(InvokedProcess::class);
        $mockProcess->shouldReceive('running')
            ->once()
            ->andReturn(false);

        Process::shouldReceive('forever')
            ->once()
            ->withNoArgs()
            ->andReturnSelf();

        Process::shouldReceive('env')
            ->once()
            ->with(['HYDE_SERVER_REQUEST_OUTPUT' => false])
            ->andReturnSelf();

        Process::shouldReceive('start')
            ->once()
            ->withArgs(function (string $command, Closure $handle) {
                $handle('type', 'foo');

                return $command === "php -S localhost:8080 {$this->binaryPath()}";
            })
            ->andReturn($mockProcess);

        $this->artisan('serve --no-ansi')
            ->expectsOutput('Starting the HydeRC server... Use Ctrl+C to stop')
            ->expectsOutput('foo')
            ->assertExitCode(0);
    }

    public function testWithFancyOutput()
    {
        Process::fake(['php -S localhost:8080 {$this->binaryPath()}' => 'foo']);

        $this->artisan('serve')
            ->expectsOutputToContain('HydePHP Realtime Compiler')
            ->assertExitCode(0);

        Process::assertRan("php -S localhost:8080 {$this->binaryPath()}");
    }

    public function testHydeServeCommandWithViteOption()
    {
        $this->cleanUpWhenDone('app/storage/framework/runtime/vite.hot');

        $this->ensureNodeEnvironmentForTest();

        $mockViteProcess = mock(InvokedProcess::class);
        $mockViteProcess->shouldReceive('running')
            ->once()
            ->andReturn(true);
        $mockViteProcess->shouldReceive('latestOutput')
            ->once()
            ->andReturn('vite latest output');

        $mockServerProcess = mock(InvokedProcess::class);
        $mockServerProcess->shouldReceive('running')
            ->times(2)
            ->andReturn(true, false);

        Process::shouldReceive('forever')
            ->twice()
            ->withNoArgs()
            ->andReturnSelf();

        Process::shouldReceive('env')
            ->once()
            ->with(['HYDE_SERVER_REQUEST_OUTPUT' => false])
            ->andReturnSelf();

        Process::shouldReceive('start')
            ->once()
            ->with('npm run dev')
            ->andReturn($mockViteProcess);

        Process::shouldReceive('start')
            ->once()
            ->withArgs(function (string $command, Closure $output) {
                $output('stdout', 'server output');

                return $command === "php -S localhost:8080 {$this->binaryPath()}";
            })
            ->andReturn($mockServerProcess);

        $this->artisan('serve --no-ansi --vite')
            ->expectsOutput('Starting the HydeRC server... Use Ctrl+C to stop')
            ->expectsOutput('server output')
            ->expectsOutput('vite latest output')
            ->assertExitCode(0);

        $this->assertFileDoesNotExist('app/storage/framework/runtime/vite.hot');
    }

    public function testHydeServeCommandWithViteOptionButViteNotRunning()
    {
        $this->cleanUpWhenDone('app/storage/framework/runtime/vite.hot');

        $this->ensureNodeEnvironmentForTest();

        $mockViteProcess = mock(InvokedProcess::class);
        $mockViteProcess->shouldReceive('running')
            ->once()
            ->andReturn(false);

        $mockServerProcess = mock(InvokedProcess::class);
        $mockServerProcess->shouldReceive('running')
            ->times(2)
            ->andReturn(true, false);

        Process::shouldReceive('forever')
            ->twice()
            ->withNoArgs()
            ->andReturnSelf();

        Process::shouldReceive('env')
            ->once()
            ->with(['HYDE_SERVER_REQUEST_OUTPUT' => false])
            ->andReturnSelf();

        Process::shouldReceive('start')
            ->once()
            ->with('npm run dev')
            ->andReturn($mockViteProcess);

        Process::shouldReceive('start')
            ->once()
            ->withArgs(function (string $command, Closure $handle) {
                return $command === "php -S localhost:8080 {$this->binaryPath()}";
            })
            ->andReturn($mockServerProcess);

        $this->artisan('serve --no-ansi --vite')
            ->expectsOutput('Starting the HydeRC server... Use Ctrl+C to stop')
            ->assertExitCode(0);

        $this->assertFileDoesNotExist('app/storage/framework/runtime/vite.hot');
    }

    public function testHydeServeCommandWithViteOptionThrowsWhenPortIsInUse()
    {
        $socket = stream_socket_server('tcp://127.0.0.1:5173', $errno, $errstr);

        if ($socket === false) {
            $this->markTestSkipped("Unable to create test socket server: $errstr (errno: $errno)");
        }

        try {
            $this->artisan('serve --vite')
                ->expectsOutputToContain('Unable to start Vite server: Port 5173 is already in use')
                ->assertExitCode(1);
        } finally {
            stream_socket_shutdown($socket, STREAM_SHUT_RDWR);
        }
    }

    public function testHydeServeCommandWithViteOptionButNodeModulesNotInstalled()
    {
        $nodeModulesExists = file_exists(Hyde::path('node_modules'));
        if ($nodeModulesExists) {
            rename(Hyde::path('node_modules'), Hyde::path('node_modules.backup'));
        }

        $packageJsonExists = file_exists(Hyde::path('package.json'));
        if (! $packageJsonExists) {
            file_put_contents(Hyde::path('package.json'), '{}');
            $this->cleanUpWhenDone('package.json');
        }

        try {
            $this->artisan('serve --vite --no-interaction')
                ->expectsOutputToContain('Node modules are not installed')
                ->assertExitCode(1);
        } finally {
            if ($nodeModulesExists) {
                rename(Hyde::path('node_modules.backup'), Hyde::path('node_modules'));
            }
        }
    }

    public function testHydeServeCommandWithViteOptionButPackageJsonMissing()
    {
        $packageJsonExists = file_exists(Hyde::path('package.json'));
        if ($packageJsonExists) {
            rename(Hyde::path('package.json'), Hyde::path('package.json.backup'));
        }

        try {
            $this->artisan('serve --vite --no-interaction')
                ->expectsOutputToContain('Node modules are not installed')
                ->assertExitCode(1);
        } finally {
            if ($packageJsonExists) {
                rename(Hyde::path('package.json.backup'), Hyde::path('package.json'));
            }
        }
    }

    public function testHydeServeCommandWithViteOptionWithInteractiveConfirmationAccepted()
    {
        $nodeModulesExists = file_exists(Hyde::path('node_modules'));
        if ($nodeModulesExists) {
            rename(Hyde::path('node_modules'), Hyde::path('node_modules.backup'));
        }

        $packageJsonExists = file_exists(Hyde::path('package.json'));
        if (! $packageJsonExists) {
            file_put_contents(Hyde::path('package.json'), '{}');
            $this->cleanUpWhenDone('package.json');
        }

        Process::fake([
            'npm install' => function () {
                if (! file_exists(Hyde::path('node_modules'))) {
                    mkdir(Hyde::path('node_modules'));
                }

                return Process::result('', '', 0);
            },
        ]);

        try {
            $this->artisan('serve --vite --no-interaction')
                ->expectsOutputToContain('Node modules are not installed')
                ->assertExitCode(1);
        } finally {
            // Restore node_modules if it existed
            if ($nodeModulesExists) {
                if (file_exists(Hyde::path('node_modules'))) {
                    $this->recursiveRemoveDirectory(Hyde::path('node_modules'));
                }
                rename(Hyde::path('node_modules.backup'), Hyde::path('node_modules'));
            } else {
                if (file_exists(Hyde::path('node_modules'))) {
                    $this->recursiveRemoveDirectory(Hyde::path('node_modules'));
                }
            }
        }
    }

    public function testHydeServeCommandWithViteOptionWithInteractiveConfirmationDeclined()
    {
        $nodeModulesExists = file_exists(Hyde::path('node_modules'));
        if ($nodeModulesExists) {
            rename(Hyde::path('node_modules'), Hyde::path('node_modules.backup'));
        }

        $packageJsonExists = file_exists(Hyde::path('package.json'));
        if (! $packageJsonExists) {
            file_put_contents(Hyde::path('package.json'), '{}');
            $this->cleanUpWhenDone('package.json');
        }

        try {
            $this->artisan('serve --vite')
                ->expectsQuestion('Would you like to install them now?', false)
                ->expectsOutputToContain('The --vite flag cannot be used if Vite is not installed')
                ->assertExitCode(1);
        } finally {
            if ($nodeModulesExists) {
                rename(Hyde::path('node_modules.backup'), Hyde::path('node_modules'));
            }
        }
    }

    public function testHydeServeCommandWithViteOptionWhenNpmInstallFails()
    {
        $nodeModulesExists = file_exists(Hyde::path('node_modules'));
        if ($nodeModulesExists) {
            rename(Hyde::path('node_modules'), Hyde::path('node_modules.backup'));
        }

        $packageJsonExists = file_exists(Hyde::path('package.json'));
        if (! $packageJsonExists) {
            file_put_contents(Hyde::path('package.json'), '{}');
            $this->cleanUpWhenDone('package.json');
        }

        Process::fake([
            'npm install' => Process::result('', 'npm install failed', 1),
        ]);

        try {
            $this->artisan('serve --vite')
                ->expectsQuestion('Would you like to install them now?', true)
                ->expectsOutput('Installing Node modules...')
                ->assertExitCode(1);

            Process::assertRan('npm install');
        } finally {
            if ($nodeModulesExists) {
                rename(Hyde::path('node_modules.backup'), Hyde::path('node_modules'));
            }
        }
    }

    public function testHydeServeCommandWithViteOptionWhenNpmInstallSucceedsButNodeModulesStillNotAvailable()
    {
        $nodeModulesExists = file_exists(Hyde::path('node_modules'));
        if ($nodeModulesExists) {
            rename(Hyde::path('node_modules'), Hyde::path('node_modules.backup'));
        }

        $packageJsonExists = file_exists(Hyde::path('package.json'));
        if (! $packageJsonExists) {
            file_put_contents(Hyde::path('package.json'), '{}');
            $this->cleanUpWhenDone('package.json');
        }

        Process::fake([
            'npm install' => Process::result('', '', 0),
        ]);

        try {
            $this->artisan('serve --vite')
                ->expectsQuestion('Would you like to install them now?', true)
                ->expectsOutput('Installing Node modules...')
                ->expectsOutput('Node modules installed successfully.')
                ->expectsOutputToContain('Node modules installation completed but dependencies are still not available')
                ->assertExitCode(1);

            Process::assertRan('npm install');
        } finally {
            if ($nodeModulesExists) {
                rename(Hyde::path('node_modules.backup'), Hyde::path('node_modules'));
            }
        }
    }

    protected function ensureNodeEnvironmentForTest(): void
    {
        if (! file_exists(Hyde::path('package.json'))) {
            file_put_contents(Hyde::path('package.json'), '{}');
            $this->cleanUpWhenDone('package.json');
        }

        if (! file_exists(Hyde::path('node_modules'))) {
            mkdir(Hyde::path('node_modules'));
            $this->cleanUpWhenDone('node_modules');
        }
    }

    protected function recursiveRemoveDirectory(string $dir): void
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object !== '.' && $object !== '..') {
                    if (is_dir($dir.'/'.$object)) {
                        $this->recursiveRemoveDirectory($dir.'/'.$object);
                    } else {
                        unlink($dir.'/'.$object);
                    }
                }
            }
            rmdir($dir);
        }
    }

    protected function binaryPath(): string
    {
        return escapeshellarg(Hyde::path('vendor/hyde/realtime-compiler/bin/server.php'));
    }
}
