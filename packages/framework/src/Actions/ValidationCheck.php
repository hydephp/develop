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
     * @var \Closure The function to call to check the test.
     *               Must return a boolean, or null if the test was skipped.
     * @example function () { return true; }
     */
    protected \Closure $check;

    /**
     * @var bool Whether the test passed.
     */
    public bool $passed;

    /**
     * @var bool Was the test skipped?
     */
    public bool $skipped = false;

    public function __construct(string $test, \Closure $check, ?string $message = null, ?string $tip = null)
    {
        $this->test = $test;
        $this->check = $check;
        $this->message = $message;
        $this->tip = $tip;
    }

    protected function run(): bool|null
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

    public function check(): bool|null
    {
        $status = $this->run();

        if ($status === true) {
            $this->pass();
            return true;
        }

        if ($status === false) {
            $this->fail();
            return false;
        }

        $this->skip();
        return null;
    }

    public function pass(): void
    {
        $this->passed = true;
        $this->message = $this->test;
    }

    public function fail(): void
    {
        $this->passed = false;
        $this->message = $this->message ?? 'Test failed: ' . $this->test;
    }

    public function skip(): void
    {
        $this->skipped = true;
        $this->message = 'Test skipped: ' . $this->test;
    }
}