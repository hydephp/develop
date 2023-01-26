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
        $this->infoComment("Setting", $name, "as the project source directory!");

        if (Filesystem::isDirectory($name) && ! Filesystem::isEmptyDirectory($name)) {
            $this->error('Directory already exists!');
            return Command::FAILED;
        }

        $this->comment('Creating directory');
        Filesystem::ensureDirectoryExists($name);


        $this->comment('Moving source directories');

        $directories = ['_pages', '_posts', '_docs'];
        foreach ($directories as $directory) {
            Filesystem::moveDirectory($directory, "$name/$directory");
        }

        
        $this->comment('Updating configuration file');

        $config = Filesystem::getContents('config/hyde.php');
        $config = str_replace("'source_root' => '',", "'source_root' => '$name',", $config);
        Filesystem::putContents('config/hyde.php', $config);

        return Command::SUCCESS;
    }
}
