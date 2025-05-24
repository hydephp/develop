<?php

declare(strict_types=1);

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

            $throwOnBusyPort = false;
            if ($throwOnBusyPort) {
                throw new RuntimeException(sprintf('Port 8080 is already in use. (PID %s)', $pid));
            } else {
                // Kill the process using the port
                shell_exec(PHP_OS_FAMILY === 'Windows' ? "taskkill /F /PID $pid" : "kill -9 $pid");
            }
        }

        // Start the server in a background process, keeping the task ID for later
        $null = PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null';
        self::$server = proc_open("php hyde serve > $null", [], $pipes, realpath(self::getRunnerPath()));

        // Wait for the server to start
        $waitTime = time();
        while (@fsockopen('localhost', 8080, $errno, $errstr, 1) === false) {
            // Timeout after 5 seconds
            if (time() - $waitTime > 5) {
                throw new RuntimeException('Failed to start the test server.');
            }

            if (proc_get_status(self::$server)['running'] === false) {
                // Make a head request to the server to see if it's running
                if (shell_exec('curl -I http://localhost:8080') === false) {
                    break;
                }
            }

            // Sleep 20ms
            usleep(20000);
        }

        // Assert that the server was started successfully
        if (! self::$server) {
            throw new RuntimeException('Failed to start the test server.');
        }
    }

    protected static function getRunnerPath(): string
    {
        // Get path from the environment variable
        $path = getenv('HYDE_RC_RUNNER_PATH');

        if ($path === false) {
            throw new RuntimeException('HYDE_RC_RUNNER_PATH environment variable is not set.');
        }

        // Check that it's not a child of the project root
        $packageDir = realpath(__DIR__.'/../../');
        if (str_starts_with($path, $packageDir)) {
            throw new RuntimeException('HYDE_RC_RUNNER_PATH cannot be a child of the package root as junctioning will massivly inflate vendor directory.');
        }

        if (file_exists($path)) {
            $path = realpath($path);
        }

        return $path;
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
        return file_exists(self::getRunnerPath()) && file_exists(self::getRunnerPath().'/vendor/autoload.php');
    }

    public static function setUpTestRunner(): void
    {
        echo "\33[33mSetting up test runner...\33[0m This may take a while.\n";

        $archive = 'https://github.com/hydephp/develop/archive/refs/heads/master.zip';
        $target = self::getRunnerPath();
        if (file_exists($target)) {
            rmdir($target);
        }

        echo "\33[33mDownloading test runner scaffolding...\33[0m\n";
        $raw = file_get_contents($archive);

        if ($raw === false) {
            throw new RuntimeException('Failed to download test runner.');
        }

        echo "\33[33mExtracting archive...\33[0m\n";
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

        if ($runner === false) {
            throw new RuntimeException('Failed to get the real path of the test runner.');
        }

        $workDir = getcwd();

        // Junction the package source of hyde/realtime-compiler to the test runner
        $branch = getenv('HYDE_RC_BRANCH') ?: trim(shell_exec('git rev-parse --abbrev-ref HEAD') ?: 'master');
        echo "\33[33mInstalling hyde/realtime-compiler:dev-$branch...\33[0m\n";
        chdir($runner);
        exec('composer config repositories.realtime-compiler path '.realpath(__DIR__.'/../../'), $output, $return);
        if ($return !== 0) {
            throw new RuntimeException('Failed to add repository path.');
        }
        exec("composer require --dev hyde/realtime-compiler:dev-$branch --no-progress", $output, $return);
        if ($return !== 0) {
            throw new RuntimeException('Failed to install hyde/realtime-compiler.');
        }
        chdir($workDir);
    }

    public function projectPath(string $path = ''): string
    {
        return realpath(self::getRunnerPath()).($path ? '/'.$path : '');
    }

    public function get(string $uri): TestResponse
    {
        return TestResponse::get($this, $uri);
    }
}
