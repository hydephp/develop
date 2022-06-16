<?php

namespace Hyde\Framework\Commands;

use Hyde\Framework\Actions\ValidationCheck;
use Hyde\Framework\Services\ValidationService;
use LaravelZero\Framework\Commands\Command;

/**
 * @see \Hyde\Testing\Feature\Commands\HydeValidateCommandTest
 */
class HydeValidateCommand extends Command
{
    protected $signature = 'validate';
    protected $description = 'Run a series of tests to validate your setup and help you optimize your site.';

    protected float $time_start;

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

        $this->info('All done!');

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
        return number_format((microtime(true) - $this->time_start) * 1000, 2);
    }
}
