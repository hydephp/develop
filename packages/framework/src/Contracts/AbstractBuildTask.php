<?php

namespace Hyde\Framework\Contracts;

use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Console\OutputStyle;

/**
 * @see \Hyde\Framework\Testing\Feature\Services\BuildHookServiceTest
 * @todo allow run method to return exit code
 */
abstract class AbstractBuildTask implements BuildTaskContract
{
    use InteractsWithIO;

    protected static string $description = 'Generic build task';

    protected float $timeStart;
    protected ?int $exitCode = null ;

    public function __construct(?OutputStyle $output = null)
    {
        $this->output = $output;
        $this->timeStart = microtime(true);
    }

    public function handle(): void
    {
        $this->write('<comment>'.$this->getDescription().'...</comment> ');

        $this->run();
        $this->then();

        $this->write("\n");
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
}
