<?php

namespace Hyde\RealtimeCompiler\Tests\Integration;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use ZipArchive;

abstract class IntegrationTestCase extends TestCase
{
    /** @var resource */
    protected static $server;

    public static function setUpBeforeClass(): void
    {
        $monorepo = realpath(__DIR__.'/../../../../');

        if ($monorepo && realpath(getcwd()) === $monorepo && file_exists($monorepo.'/hyde')) {
            throw new InvalidArgumentException('This test suite is not intended to be run from the monorepo.');
        }

        if (! self::hasTestRunnerSetUp()) {
            self::setUpTestRunner();
        }

        // Check that post 8080 is available
        $process = @fsockopen('localhost', 8080, $errno, $errstr, 1);

        if ($process) {
            // Try to find the PID of the process using the port
            if (PHP_OS_FAMILY === 'Windows') {
                $raw = shell_exec('netstat -aon | findstr :8080');
                // Get the PID of the process (last column of the first line)
                preg_match('/\s+(\d+)$/', explode("\n", $raw)[0], $matches);
                $pid = trim($matches[1]);
            } else {
                $pid = shell_exec('lsof -t -i:8080');
            }

            fclose($process);
            $throwInsteadOfKill = false;
            if ($throwInsteadOfKill) {
                throw new RuntimeException(sprintf('Port 8080 is already in use. (PID %s)', $pid));
            } else {
                // Kill the process using the port
                shell_exec(PHP_OS_FAMILY === 'Windows' ? "taskkill /F /PID $pid" : "kill -9 $pid");
            }
        }

        // Start the server in a background process, keeping the task ID for later
        $null = PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null';
        self::$server = proc_open("php hyde serve > $null", [], $pipes, realpath(__DIR__.'/../runner'));

        // Wait for the server to start
        while (@fsockopen('localhost', 8080, $errno, $errstr, 1) === false) {
            if (proc_get_status(self::$server)['running'] === false) {
                break;
            }
        }

        // Assert that the server was started successfully
        if (! self::$server) {
            throw new RuntimeException('Failed to start the test server.');
        }
    }

    public function __destruct()
    {
        // Ensure the server is stopped, regardless of any errors
        if (self::$server) {
            proc_terminate(self::$server);
        }
    }

    protected static function hasTestRunnerSetUp(): bool
    {
        return file_exists(__DIR__.'/../runner');
    }

    public static function setUpTestRunner(): void
    {
        echo "\33[33mSetting up test runner...\33[0m This may take a while.\n";

        $archive = 'https://github.com/hydephp/hyde/archive/refs/heads/master.zip';
        $target = __DIR__.'/../runner';

        $raw = file_get_contents($archive);

        if ($raw === false) {
            throw new RuntimeException('Failed to download test runner.');
        }

        $zipPath = tempnam(sys_get_temp_dir(), 'hyde-master');

        if ($zipPath === false) {
            throw new RuntimeException('Failed to create temporary file.');
        }

        file_put_contents($zipPath, $raw);

        $zip = new ZipArchive();

        if ($zip->open($zipPath) !== true) {
            throw new RuntimeException('Failed to open zip archive.');
        }

        // Get the name of the root directory in the zip file
        $rootDir = $zip->getNameIndex(0);

        // Extract to a temporary directory
        $tempExtractPath = $target.'_temp';
        $zip->extractTo($tempExtractPath);

        $zip->close();

        // Move the contents of the extracted directory to the target directory
        rename($tempExtractPath.'/'.$rootDir, $target);

        // Remove the temporary extraction directory
        rmdir($tempExtractPath);

        unlink($zipPath);

        $runner = realpath($target);

        // Junction the package source of hyde/realtime-compiler to the test runner
        $branch = trim(shell_exec('git rev-parse --abbrev-ref HEAD') ?: 'master');
        shell_exec("cd $runner && composer config repositories.realtime-compiler path ../../");
        shell_exec("cd $runner && composer require --dev hyde/realtime-compiler:dev-$branch --no-progress > setup.log 2>&1");
    }

    public function projectPath(string $path = ''): string
    {
        return realpath(__DIR__.'/../runner').($path ? '/'.$path : '');
    }

    public function get(string $uri): TestResponse
    {
        return TestResponse::get($this, $uri);
    }
}
