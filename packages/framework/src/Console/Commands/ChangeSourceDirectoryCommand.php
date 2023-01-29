<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Console\Concerns\Command;
use Hyde\Facades\Filesystem;
use Hyde\Framework\Exceptions\FileConflictException;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\HtmlPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;

use function array_unique;
use function basename;
use function config;
use function is_dir;
use function is_file;
use function realpath;
use function scandir;
use function str_replace;

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
        try {
            $name = (string) $this->argument('name');
            if (realpath(Hyde::path($name)) === realpath(Hyde::path(config('hyde.source_root', '')))) {
                throw new FileConflictException(message: "The directory '$name' is already set as the project source root!");
            }
            $this->infoComment('Setting', $name, 'as the project source directory!');

            $directories = array_unique([
                HtmlPage::$sourceDirectory,
                BladePage::$sourceDirectory,
                MarkdownPage::$sourceDirectory,
                MarkdownPost::$sourceDirectory,
                DocumentationPage::$sourceDirectory,
            ]);

            if (Filesystem::isDirectory($name) && ! Filesystem::isEmptyDirectory($name)) {
                foreach ($directories as $directory) {
                    $directory = "$name/".basename($directory);
                    if (self::isNonEmptyDirectory(Hyde::path($directory))) {
                        throw new FileConflictException(message: 'Directory already exists!');
                    }
                }
            }
        } catch (FileConflictException $e) {
            $this->error($e->getMessage());

            return 409;
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
        if (str_contains($config, $search)) {
            $config = str_replace($search, "'source_root' => '$name',", $config);
            Filesystem::putContents('config/hyde.php', $config);
        } else {
            $this->error('Automatic configuration update failed, to finalize the change, please set the `source_root` setting to '."'$name'".' in `config/hyde.php`');
        }

        // We could also check if there are any more page classes (from packages) and add a note that they may need manual attention

        $this->info('All done!');

        return Command::SUCCESS;
    }

    protected static function isNonEmptyDirectory(string $directory): bool
    {
        if (is_file($directory)) {
            return true;
        }

        return is_dir($directory) && (count(scandir($directory)) > 2);
    }
}
