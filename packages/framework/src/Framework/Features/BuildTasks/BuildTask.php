<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\BuildTasks;

use Hyde\Framework\Concerns\TracksExecutionTime;
use Hyde\Hyde;
use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Command\Command;
use Throwable;

/**
 * @see \Hyde\Framework\Testing\Feature\Services\BuildTaskServiceTest
 */
abstract class BuildTask
{
    use InteractsWithIO;
    use TracksExecutionTime;

    protected static string $message = 'Running generic build task';

    protected int $exitCode = Command::SUCCESS;

    /** @var \Illuminate\Console\OutputStyle|null */
    protected $output;

    abstract public function run(): void;

    /** @phpstan-consistent-constructor */
    public function __construct(?OutputStyle $output = null)
    {
        $this->output = $output;
    }

    public function handle(): int
    {
        $this->startClock();

        $this->write("<comment>{$this->getDescription()}...</comment> ");

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

    public function then(): void
    {
        $this->writeln('<fg=gray>Done in '.$this->getExecutionTimeString().'</>');
    }

    public function getDescription(): string
    {
        return static::$message;
    }

    public function write(string $message): void
    {
        $this->output?->write($message);
    }

    public function writeln(string $message): void
    {
        $this->output?->writeln($message);
    }

    /** Write a fluent message to the output that the task created the specified file. */
    public function createdSiteFile(string $path): static
    {
        $this->write(sprintf(
            "\n > Created <info>%s</info>",
            str_replace('\\', '/', Hyde::pathToRelative($path))
        ));

        return $this;
    }

    /** Write a fluent message to the output with the execution time of the task. */
    public function withExecutionTime(): static
    {
        $this->write(" in {$this->getExecutionTimeString()}");

        return $this;
    }
}
