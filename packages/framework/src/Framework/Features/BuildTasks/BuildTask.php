<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\BuildTasks;

use Hyde\Framework\Concerns\TracksExecutionTime;
use Hyde\Hyde;
use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Console\OutputStyle;
use Throwable;

/**
 * @see \Hyde\Framework\Testing\Feature\Services\BuildTaskServiceTest
 */
abstract class BuildTask
{
    use InteractsWithIO;
    use TracksExecutionTime;

    /** @todo Consider renaming to $message */
    protected static string $description = 'Generic build task';

    /**
     * @todo Consider setting default value to 0
     */
    protected ?int $exitCode = null;

    /** @var \Illuminate\Console\OutputStyle|null */
    protected $output;

    public function __construct(?OutputStyle $output = null)
    {
        $this->startClock();
        $this->output = $output;
    }

    public function handle(): ?int
    {
        $this->write('<comment>'.$this->getDescription().'...</comment> ');

        try {
            $this->run();
            $this->then();
        } catch (Throwable $exception) {
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
        $this->writeln('<fg=gray>Done in '.$this->getExecutionTimeString().'</>');
    }

    public function getDescription(): string
    {
        return static::$description;
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
        $this->write(" in {$this->getExecutionTimeString()}");

        return $this;
    }
}
