<?php

namespace Hyde\Framework\Actions;

/**
 * @see \Hyde\Testing\Feature\Services\ValidationServiceTest
 */
class ValidationCheck
{
    /**
     * @var string The name of the test.
     * @example 'True is true'
     */
    protected string $test;

    /**
     * @var ?string The message to display if the test fails.
     * @example 'Failed asserting that true is true.'
     */
    protected ?string $message;

    /**
     * @var ?string $tip Optional tip to display if the test fails.
     */
    protected ?string $tip;

    /**
     * @var \Closure The function to call to check the test. Must return a boolean.
     * @example function () { return true; }
     */
    protected \Closure $check;

    /**
     * @var bool Whether the test passed.
     */
    public bool $passed;

    public function __construct(string $test, \Closure $check, ?string $message = null, ?string $tip = null)
    {
        $this->test = $test;
        $this->check = $check;
        $this->message = $message;
        $this->tip = $tip;
    }

    protected function run(): bool
    {
        return ($this->check)();
    }

    public function passed(): bool
    {
        return $this->passed;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function tip(): ?string
    {
        return $this->tip;
    }

    public function check(): bool
    {
        if ($this->run() === true) {
            $this->passed = true;
            $this->message = $this->test;
            return true;
        }

        $this->passed = false;
        $this->message = $this->message ?? 'Test failed: ' . $this->test;
        return false;
    }
}