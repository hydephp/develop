<?php

namespace Hyde\Framework\Actions;

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
     * @var \Closure The function to call to check the test. Must return a boolean.
     * @example function () { return true; }
     */
    protected \Closure $check;

    public bool $passed;

    public function __construct(string $test, \Closure $check, ?string $message)
    {
        $this->test = $test;
        $this->check = $check;
        $this->message = $message;
    }

    public function passed(): bool
    {
        return $this->passed;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function run(): void
    {
        if (($this->check)() === true) {
            $this->passed = true;
            $this->message = $this->test;
            return;
        }

        $this->passed = false;
        $this->message = $this->message ?? 'Test failed: ' . $this->test;
    }
}