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

    public function handle(): int
    {
        $this->info('Running validation tests!');

        foreach (ValidationService::checks() as $check) {
            $this->check($check);
        }

        $this->info('All done!');

        return 0;
    }

    protected function check(ValidationCheck $validation): void
    {
        $validation->check();

        if ($validation->passed()) {
            $this->passed($validation->message());
        } else {
            $this->failed($validation->message());
        }
    }

    protected function passed(string $message): void
    {
        $this->info($message);
    }

    protected function failed(string $message): void
    {
        $this->error($message);
    }
}
