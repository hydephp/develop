<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use LaravelZero\Framework\Commands\Command;

/**
 * @see \Hyde\Framework\Testing\Feature\Commands\ChangeSourceDirectoryCommandTest
 */
class ChangeSourceDirectoryCommand extends Command
{
    /** @var string */
    protected $signature = 'change:sourceDirectory';

    /** @var string */
    protected $description = 'Change the source directory for your project.';

    public function handle(): int
    {
        //

        return Command::SUCCESS;
    }
}
