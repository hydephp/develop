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

        $directories = array_unique([
            \Hyde\Pages\HtmlPage::$sourceDirectory,
            \Hyde\Pages\BladePage::$sourceDirectory,
            \Hyde\Pages\MarkdownPage::$sourceDirectory,
            \Hyde\Pages\MarkdownPost::$sourceDirectory,
            \Hyde\Pages\DocumentationPage::$sourceDirectory,
        ]);

        if (Filesystem::isDirectory($name) && ! Filesystem::isEmptyDirectory($name)) {
            foreach ($directories as $directory) {
                if (Filesystem::isDirectory($directory) && ! Filesystem::isEmptyDirectory($directory)) {
                    $this->error('Directory already exists!');
                    return Command::FAILURE;
                }
            }
        }

        $this->comment('Creating directory');
        Filesystem::ensureDirectoryExists($name);


        $this->comment('Moving source directories');

        foreach ($directories as $directory) {
            Filesystem::moveDirectory($directory, "$name/".basename($directory));
        }

        
        $this->updateConfigurationFile($name);

        // We could also check if there are any more page classes (from packages) and add a note that they may need manual attention

        $this->info('All done!');

        return Command::SUCCESS;
    }

    protected function updateConfigurationFile(string $name): void
    {
        $this->comment('Updating configuration file');

        $config = Filesystem::getContents('config/hyde.php');
        if (! str_contains($config, "'source_root' => '',")) {
            // We could also inject the current source root value and check if the setting is even present, but we're keeping it simple for now
            $this->error('Automatic configuration update failed, to finalize the change, please set the `source_root` setting to '. "'$name'". ' in `config/hyde.php`');
        } else {
            $config = str_replace("'source_root' => '',", "'source_root' => '$name',", $config);
            Filesystem::putContents('config/hyde.php', $config);
        }
    }
}
