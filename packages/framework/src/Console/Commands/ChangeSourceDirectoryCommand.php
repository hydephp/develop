<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use function array_unique;
use function basename;
use function config;
use Hyde\Console\Concerns\Command;
use Hyde\Facades\Filesystem;
use Hyde\Hyde;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\HtmlPage;
use Hyde\Pages\MarkdownPage;
use Hyde\Pages\MarkdownPost;
use InvalidArgumentException;
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
            $newDirectoryName = $this->getValidatedName((string) $this->argument('name'));
        } catch (InvalidArgumentException $exception) {
            $this->error($exception->getMessage());

            return 409;
        }

        $this->comment('Creating directory');
        Filesystem::ensureDirectoryExists($newDirectoryName);

        $this->comment('Moving source directories');

        foreach ($this->getPageDirectories() as $directory) {
            Filesystem::moveDirectory($directory, $this->assembleSubdirectoryPath($newDirectoryName, $directory));
        }

        $this->comment('Updating configuration file');

        $current = (string) config('hyde.source_root', '');
        $search = "'source_root' => '$current',";

        $config = Filesystem::getContents('config/hyde.php');
        if (str_contains($config, $search)) {
            $config = str_replace($search, "'source_root' => '$newDirectoryName',", $config);
            Filesystem::putContents('config/hyde.php', $config);
        } else {
            $this->error('Automatic configuration update failed, to finalize the change, please set the `source_root` setting to '."'$newDirectoryName'".' in `config/hyde.php`');
        }

        // We could also check if there are any more page classes (from packages) and add a note that they may need manual attention

        $this->info('All done!');

        return Command::SUCCESS;
    }

    /** @return string[] */
    protected function getPageDirectories(): array
    {
        return array_unique([
            HtmlPage::$sourceDirectory,
            BladePage::$sourceDirectory,
            MarkdownPage::$sourceDirectory,
            MarkdownPost::$sourceDirectory,
            DocumentationPage::$sourceDirectory,
        ]);
    }

    protected function getValidatedName(string $name): string
    {
        $this->validateName($name);
        $this->validateDirectoryCanBeUsed($name, $this->getPageDirectories());
        $this->infoComment('Setting', $name, 'as the project source directory!');

        return $name;
    }

    protected function validateName(string $name): void
    {
        if (realpath(Hyde::path($name)) === realpath(Hyde::path(config('hyde.source_root', '')))) {
            throw new InvalidArgumentException("The directory '$name' is already set as the project source root!");
        }
    }

    protected function validateDirectoryCanBeUsed(string $name, array $pageDirectories): void
    {
        if (Filesystem::isFile($name)) {
            throw new InvalidArgumentException('A file already exists at this path!');
        }

        if (Filesystem::isDirectory($name) && ! Filesystem::isEmptyDirectory($name)) {
            // If any of the subdirectories we want to move already exist, we need to abort as we don't want to overwrite any existing files
            // The reason we check these individually is mainly so that the change can be reverted (by setting the $name to '/')
            foreach ($pageDirectories as $directory) {
                $subdirectoryPath = $this->assembleSubdirectoryPath($name, $directory);
                if (is_file($subdirectoryPath) || $this->directoryContainsFiles($subdirectoryPath)) {
                    throw new InvalidArgumentException('Directory already exists!');
                }
            }
        }
    }

    protected function assembleSubdirectoryPath(string $name, string $subdirectory): string
    {
        return Hyde::path("$name/".basename($subdirectory));
    }

    protected function directoryContainsFiles(string $subdirectory): bool
    {
        return is_dir($subdirectory) && (count(scandir($subdirectory)) > 2);
    }
}
