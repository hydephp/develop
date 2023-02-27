<?php

declare(strict_types=1);

use JetBrains\PhpStorm\NoReturn;

class MicroTest
{
    protected static self $instance;

    protected array $tests = [];
    protected array $failedTests = [];

    protected function __construct()
    {
        echo 'Running tests...'.PHP_EOL;
    }

    #[NoReturn]
    public function __destruct()
    {
        $this->run();
    }

    public static function getInstance(): self
    {
        if (! isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function test(string $name, Closure $callback): void
    {
        $callback = $callback->bindTo($this);

        $this->tests[$name] = $callback;
    }

    /** @throws \Exception */
    public function assert(bool $condition, string $message = null): void
    {
        if (! $condition) {
            throw new Exception($message ?? 'Assertion failed');
        }
    }

    #[NoReturn]
    protected function run(): void
    {
        foreach ($this->tests as $name => $test) {
            try {
                $test();
            } catch (Exception $exception) {
                $this->failedTests[$name] = $exception;
            } finally {
                $status = (isset($exception) ? 'failed' : 'passed');
                echo "Test $status: $name".PHP_EOL;
                if (isset($exception)) {
                    echo " > {$exception->getMessage()}".PHP_EOL;
                }
                unset($exception);
            }
        }

        if (count($this->failedTests) > 0) {
            echo 'Some tests failed'.PHP_EOL;
            exit(1);
        }

        echo 'All tests passed'.PHP_EOL;
        exit(0);
    }
}

function test(string $name, callable $callback): void
{
    MicroTest::getInstance()->test($name, $callback);
}
