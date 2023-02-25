<?php

declare(strict_types=1);

namespace Hyde\Support;

use Hyde\Facades\Config;

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

    public static function getWarnings(): array
    {
        return self::getInstance()->get();
    }

    public static function hasWarnings(): bool
    {
        return count(self::getInstance()->warnings) > 0;
    }

    public static function reportsWarnings(): bool
    {
        return Config::getBool('hyde.log_warnings', true);
    }

    public function add(string $warning): void
    {
        $this->warnings[] = $warning;
    }

    public function get(): array
    {
        return $this->warnings;
    }

    public function clear(): void
    {
        $this->warnings = [];
    }
}
