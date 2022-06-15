<?php

namespace Hyde\Framework\Commands;

use Hyde\Framework\Actions\ValidationCheck;
use Hyde\Framework\Services\ValidationService;
use LaravelZero\Framework\Commands\Command;

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
            $this->info($validation->message());
        } else {
            $this->error($validation->message());
        }
    }
}
