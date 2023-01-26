<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Console\Concerns\Command;
use Hyde\Facades\Filesystem;

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
        $name = $this->argument('name');
        $this->info("Setting $name as the project source directory!");

        if (Filesystem::isDirectory($name) && ! Filesystem::isEmptyDirectory($name)) {
            $this->error('Directory already exists!');
            return Command::FAILED;
        }

        Filesystem::ensureDirectoryExists($name);

        return Command::SUCCESS;
    }
}
