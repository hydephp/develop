<?php

declare(strict_types=1);

namespace Hyde\Console\Helpers;

use Hyde\Facades\Filesystem;
use Hyde\Foundation\Providers\ViewServiceProvider;
use Hyde\Hyde;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

use function Hyde\path_join;
use function Laravel\Prompts\multiselect;

/**
 * @internal This class offloads logic from the PublishViewsCommand class and should not be used elsewhere.
 */
class InteractivePublishCommandHelper
{
    protected readonly string $group;

    public function __construct(string $group)
    {
        $this->group = $group;
    }

    public function handle(): string
    {
        $group = $this->group;

        // Get all files in the components tag
        $paths = ServiceProvider::pathsToPublish(ViewServiceProvider::class, $group);
        $source = key($paths);
        $target = $paths[$source];

        // Now we need an array that maps all source files to their target paths retaining the directory structure
        $search = File::allFiles($source);

        $files = collect($search)->mapWithKeys(/** @return array<string, string> */ function (SplFileInfo $file) use ($target): array {
            $targetPath = path_join($target, $file->getRelativePathname());

            return [Hyde::pathToRelative(realpath($file->getPathname())) => Hyde::pathToRelative($targetPath)];
        });

        // Now we need to prompt the user for which files to publish
        $selectedFiles = $this->promptForFiles($files, basename($target));

        // Now we filter the files to only include the selected ones
        $selectedFiles = $files->filter(fn (string $file): bool => in_array($file, $selectedFiles));

        // Now we need to publish the selected files
        foreach ($selectedFiles as $source => $target) {
            Filesystem::ensureDirectoryExists(dirname($target));
            Filesystem::copy($source, $target);
        }

        $message = sprintf('Published files [%s]', collect($selectedFiles)->map(fn (string $file): string => Str::after($file, basename($source).'/'))->implode(', '));

        return $message;
    }

    protected function promptForFiles(Collection $files, string $baseDir): array
    {
        $choices = $files->mapWithKeys(/** @return array<string, string> */ function (string $source) use ($baseDir): array {
            return [$source => Str::after($source, $baseDir.'/')];
        });

        return multiselect('Select the files you want to publish (CTRL+A to toggle all)', $choices, [], 10, 'required', hint: 'Navigate with arrow keys, space to select, enter to confirm.');
    }
}
