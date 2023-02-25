<?php

declare(strict_types=1);

namespace Hyde\Support;

use Hyde\Facades\Config;
use Hyde\Framework\Exceptions\BuildWarning;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Symfony\Component\Console\Style\OutputStyle;

/**
 * @experimental
 *
 * @see \Hyde\Framework\Testing\Unit\BuildWarningsTest
 */
class BuildWarnings
{
    protected static self $instance;

    /** @var array<\Hyde\Framework\Exceptions\BuildWarning> */
    protected array $warnings = [];

    public static function getInstance(): static
    {
        if (! isset(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public static function report(string $warning, string $location = ''): void
    {
        static::getInstance()->add(new BuildWarning($warning, $location));
    }

    /** @return array<\Hyde\Framework\Exceptions\BuildWarning> */
    public static function getWarnings(): array
    {
        return static::getInstance()->get();
    }

    public static function hasWarnings(): bool
    {
        return count(static::getInstance()->warnings) > 0;
    }

    public static function reportsWarnings(): bool
    {
        return Config::getBool('hyde.log_warnings', true);
    }

    public static function writeWarningsToOutput(OutputStyle $output, bool $verbose = false): void
    {
        foreach (static::getWarnings() as $line => $warning) {
            if (static::reportsWarningsAsExceptions()) {
                app(ExceptionHandler::class)->renderForConsole($output, $warning);
            } else {
                $output->writeln(sprintf(' %s. <comment>%s</comment>', $line + 1, $warning->getMessage()));
                if ($warning->getLocation()) {
                    $output->writeln(sprintf('    <fg=gray>%s:%s</>', $warning->getFile(), $warning->getLine()));
                }
            }
        }
    }

    public function add(BuildWarning $warning): void
    {
        $this->warnings[] = $warning;
    }

    /** @return array<\Hyde\Framework\Exceptions\BuildWarning> */
    public function get(): array
    {
        return $this->warnings;
    }

    public function clear(): void
    {
        $this->warnings = [];
    }

    protected static function reportsWarningsAsExceptions(): bool
    {
        return Config::getBool('hyde.convert_build_warnings_to_exceptions', false);
    }
}
