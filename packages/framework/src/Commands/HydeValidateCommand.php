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

    protected function check(ValidationCheck $validation): void
    {
        $this->time_start = microtime(true);
        $validation->check();

        if ($validation->skipped()) {
            $this->line($validation->message());
        } else {
            if ($validation->passed()) {
                $this->passed($validation);
            } else {
                $this->failed($validation);
            }
        }

        $this->newline();
    }

    protected function passed(ValidationCheck $validation): void
    {
        $this->info($validation->message() . $this->time());
    }

    protected function failed(ValidationCheck $validation): void
    {
        $this->error($validation->message() . $this->time());
        if ($validation->tip()) {
            $this->comment($validation->tip());
        }
    }

    protected function time(): string
    {
        return '<fg=gray> (' . number_format((microtime(true) - $this->time_start) * 1000, 2) . 'ms)</>';
    }
}
