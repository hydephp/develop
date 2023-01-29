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
            $name = $this->validatedName((string) $this->argument('name'));
        } catch (InvalidArgumentException $exception) {
            $this->error($exception->getMessage());

            return 409;
        }

        $this->comment('Creating directory');
        Filesystem::ensureDirectoryExists($name);

        $this->comment('Moving source directories');

        foreach ($this->getPageDirectories() as $directory) {
            Filesystem::moveDirectory($directory, $this->assembleNewSubdirectoryPath($name, $directory));
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

    protected function validatedName(string $name): string
    {
        $this->validateName($name);
        $this->infoComment('Setting', $name, 'as the project source directory!');

        $this->validateDirectory($name, $this->getPageDirectories());

        return $name;
    }

    protected function validateName(string $name): void
    {
        if (realpath(Hyde::path($name)) === realpath(Hyde::path(config('hyde.source_root', '')))) {
            throw new InvalidArgumentException("The directory '$name' is already set as the project source root!");
        }
    }

    protected function validateDirectory(string $name, array $pageDirectories): void
    {
        if (Filesystem::isDirectory($name) && ! Filesystem::isEmptyDirectory($name)) {
            // If any of the subdirectories we want to move already exist, we need to abort
            foreach ($pageDirectories as $directory) {
                if ($this->directoryContainsFiles(Hyde::path($this->assembleNewSubdirectoryPath($name, $directory)))) {
                    throw new InvalidArgumentException('Directory already exists!');
                }
            }
        }
    }

    protected function assembleNewSubdirectoryPath(string $name, string $subdirectory): string
    {
        return "$name/".basename($subdirectory);
    }

    protected function directoryContainsFiles(string $directory): bool
    {
        return is_file($directory) || (is_dir($directory) && (count(scandir($directory)) > 2));
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
}
