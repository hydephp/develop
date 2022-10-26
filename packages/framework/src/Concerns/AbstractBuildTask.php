<?php
declare(strict_types=1);

namespace Hyde\Framework\Concerns;

use Hyde\Framework\Contracts\BuildTaskContract;
use Hyde\Framework\Hyde;
use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Console\OutputStyle;

/**
 * @see \Hyde\Framework\Testing\Feature\Services\BuildTaskServiceTest
 */
abstract class AbstractBuildTask implements BuildTaskContract
{
    use InteractsWithIO;

    protected static string $description = 'Generic build task';

    protected float $timeStart;
    protected ?int $exitCode = null;

    public function __construct(?OutputStyle $output = null)
    {
        $this->output = $output;
        $this->timeStart = microtime(true);
    }

    public function handle(): ?int
    {
        $this->write('<comment>'.$this->getDescription().'...</comment> ');

        try {
            $this->run();
            $this->then();
        } catch (\Throwable $exception) {
            $this->writeln('<error>Failed</error>');
            $this->writeln("<error>{$exception->getMessage()}</error>");
            $this->exitCode = $exception->getCode();
        }

        $this->write("\n");

        return $this->exitCode;
    }

    abstract public function run(): void;

    public function then(): void
    {
        $this->writeln('<fg=gray>Done in '.$this->getExecutionTime().'</>');
    }

    public function getDescription(): string
    {
        return static::$description;
    }

    public function getExecutionTime(): string
    {
        return number_format((microtime(true) - $this->timeStart) * 1000, 2).'ms';
    }

    public function write(string $message): void
    {
        $this->output?->write($message);
    }

    public function writeln(string $message): void
    {
        $this->output?->writeln($message);
    }

    public function createdSiteFile(string $path): static
    {
        $this->write(sprintf(
            "\n > Created <info>%s</info>",
            str_replace('\\', '/', Hyde::pathToRelative($path))
        ));

        return $this;
    }

    public function withExecutionTime(): static
    {
        $this->write(" in {$this->getExecutionTime()}");

        return $this;
    }
}
