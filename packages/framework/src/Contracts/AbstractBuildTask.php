<?php

namespace Hyde\Framework\Contracts;

use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Console\OutputStyle;

/**
 * @experimental This feature was recently added and may be changed without notice.
 * @since 0.40.0
 */
abstract class AbstractBuildTask implements BuildTaskContract
{
    use InteractsWithIO;

    protected static string $description = 'Generic build task';

    protected float $timeStart;

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

        $this->newLine();
    }

    abstract public function run(): void;

    public function then(): void
    {
        $this->write('<info>Done.</info> '.$this->getExecutionTime());
    }

    public function getDescription(): string
    {
        return static::$description;
    }

    public function getExecutionTime(): string
    {
        return number_format((microtime(true) - $this->timeStart) * 1000, 2) . 'ms';
    }

    public function write(string $message): void
    {
        $this->output->write($message);
    }

    public function writeln(string $message): void
    {
        $this->output->writeln($message);
    }
}
