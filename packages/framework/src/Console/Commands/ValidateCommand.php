<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Framework\Concerns\TracksExecutionTime;
use Hyde\Framework\Services\ValidationService;
use LaravelZero\Framework\Commands\Command;

/**
 * @see \Hyde\Framework\Testing\Feature\Commands\ValidateCommandTest
 */
class ValidateCommand extends Command
{
    use TracksExecutionTime;

    /** @var string */
    protected $signature = 'validate';

    /** @var string */
    protected $description = 'Run a series of tests to validate your setup and help you optimize your site.';

    protected ValidationService $service;

    public function __construct()
    {
        parent::__construct();

        $this->service = new ValidationService();
    }

    public function handle(): int
    {
        $this->startClock();

        $this->info('Running validation tests!');

        $this->newLine();

        foreach (ValidationService::checks() as $check) {
            $this->check($check);
        }

        $this->info('All done! '.$this->timeTotal());

        return Command::SUCCESS;
    }

    protected function check(string $check): void
    {
        $timeStart = microtime(true);
        $result = $this->service->run($check);

        $this->line($result->formattedMessage($this->time($timeStart)));

        $this->newline();
    }

    protected function time(float $timeStart): string
    {
        return number_format((microtime(true) - $timeStart) * 1000, 2);
    }

    protected function timeTotal(): string
    {
        return'<fg=gray>Ran '.sizeof(ValidationService::checks()).' checks in '.$this->getExecutionTimeString().'</>';
    }
}
