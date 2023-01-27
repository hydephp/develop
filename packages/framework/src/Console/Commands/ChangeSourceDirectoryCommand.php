<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Console\Concerns\Command;
use Hyde\Facades\Filesystem;
use Hyde\Hyde;

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
        $name = (string) $this->argument('name');
        if (realpath(Hyde::path($name)) === realpath(Hyde::path(config('hyde.source_root', '')))) {
            $this->error("The directory '$name' is already set as the project source root!");

            return 409;
        }
        $this->infoComment('Setting', $name, 'as the project source directory!');

        $directories = array_unique([
            \Hyde\Pages\HtmlPage::$sourceDirectory,
            \Hyde\Pages\BladePage::$sourceDirectory,
            \Hyde\Pages\MarkdownPage::$sourceDirectory,
            \Hyde\Pages\MarkdownPost::$sourceDirectory,
            \Hyde\Pages\DocumentationPage::$sourceDirectory,
        ]);

        if (Filesystem::isDirectory($name) && ! Filesystem::isEmptyDirectory($name)) {
            foreach ($directories as $directory) {
                $directory = "$name/".basename($directory);
                if (self::isNonEmptyDirectory(Hyde::path($directory))) {
                    $this->error('Directory already exists!');

                    return 409;
                }
            }
        }

        $this->comment('Creating directory');
        Filesystem::ensureDirectoryExists($name);

        $this->comment('Moving source directories');

        foreach ($directories as $directory) {
            Filesystem::moveDirectory($directory, "$name/".basename($directory));
        }

        $this->comment('Updating configuration file');

        $current = (string) config('hyde.source_root', '');
        $search = "'source_root' => '$current',";

        $config = Filesystem::getContents('config/hyde.php');
        if (! str_contains($config, $search)) {
            $this->error('Automatic configuration update failed, to finalize the change, please set the `source_root` setting to '."'$name'".' in `config/hyde.php`');
        } else {
            $config = str_replace($search, "'source_root' => '$name',", $config);
            Filesystem::putContents('config/hyde.php', $config);
        }

        // We could also check if there are any more page classes (from packages) and add a note that they may need manual attention

        $this->info('All done!');

        return Command::SUCCESS;
    }

    protected static function isNonEmptyDirectory(string $directory): bool
    {
        if (is_file($directory)) {
            return false;
        }

        return is_dir($directory) && (count(scandir($directory)) > 2);
    }
}
