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
    protected $signature = 'change:sourceDirectory {name : The new source directory name }';

    /** @var string */
    protected $description = 'Change the source directory for your project.';

    protected $hidden = true;

    public function handle(): int
    {
        //

        return Command::SUCCESS;
    }
}
