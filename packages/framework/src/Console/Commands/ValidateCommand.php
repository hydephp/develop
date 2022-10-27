<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Framework\Features\Validation\ValidationService;
use LaravelZero\Framework\Commands\Command;

/**
 * @see \Hyde\Framework\Testing\Feature\Commands\ValidateCommandTest
 */
class ValidateCommand extends Command
{
    /** @var string */
    protected $signature = 'validate';

    /** @var string */
    protected $description = 'Run a series of tests to validate your setup and help you optimize your site.';

    protected float $time_start;
    protected float $time_total = 0;

    protected ValidationService $service;

    public function __construct()
    {
        parent::__construct();

        $this->service = new ValidationService();
    }

    public function handle(): int
    {
        $this->info('Running validation tests!');

        $this->newLine();

        foreach (ValidationService::checks() as $check) {
            $this->check($check);
        }

        $this->info('All done! '.$this->timeTotal());

        return 0;
    }

    protected function check(string $check): void
    {
        $this->time_start = microtime(true);

        $result = $this->service->run($check);

        $this->line($result->formattedMessage($this->time()));

        $this->newline();
    }

    protected function time(): string
    {
        $time = (microtime(true) - $this->time_start) * 1000;
        $this->time_total += $time;

        return number_format($time, 2);
    }

    protected function timeTotal(): string
    {
        return'<fg=gray>Ran '.sizeof(ValidationService::checks()).' checks in '.number_format($this->time_total, 2).'ms</>';
    }
}
