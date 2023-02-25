<?php

declare(strict_types=1);

namespace Hyde\Support;

/**
 * @experimental
 */
class BuildWarnings
{
    protected static self $instance;

    protected array $warnings = [];

    public static function getInstance(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function report(string $warning): void
    {
        self::getInstance()->add($warning);
    }

    public static function warnings(): array
    {
        return self::getInstance()->get();
    }

    public function add(string $warning): void
    {
        $this->warnings[] = $warning;
    }

    public function get(): array
    {
        return $this->warnings;
    }

    public function hasWarnings(): bool
    {
        return count($this->warnings) > 0;
    }

    public function clear(): void
    {
        $this->warnings = [];
    }
}
