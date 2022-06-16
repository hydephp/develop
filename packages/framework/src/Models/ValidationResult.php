<?php

namespace Hyde\Framework\Models;

class ValidationResult
{
    public string $message;
    public string $tip;

    public bool $passed;
    public bool $skipped = false;

    public function __construct(string $defaultMessage = 'Generic check')
    {
        $this->message = $defaultMessage;
    }

    public function pass(?string $withMessage = null): self
    {
        $this->passed = true;
        if ($withMessage) {
            $this->message = $withMessage;
        }
        return $this;
    }

    public function fail(?string $withMessage = null): self
    {
        $this->passed = false;
        if ($withMessage) {
            $this->message = $withMessage;
        }
        return $this;
    }

    public function skip(?string $withMessage = null): self
    {
        $this->skipped = true;
        if ($withMessage) {
            $this->message = $withMessage;
        }
        return $this;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function tip(): string|false
    {
        return $this->tip ?? false;
    }

    public function passed(): bool
    {
        return $this->passed;
    }

    public function failed(): bool
    {
        return ! $this->passed;
    }

    public function skipped(): bool
    {
        return $this->skipped;
    }
}