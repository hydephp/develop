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
    protected readonly string $source;
    protected readonly string $target;

    public function __construct(string $group)
    {
        $this->group = $group;
    }

    public function handle(): string
    {
        $filesInTag = $this->findAllFilesForTag();
        $publishableFilesMap = $this->mapPublishableFiles($filesInTag);

        $selectedFiles = $this->promptForFiles($publishableFilesMap, basename($this->target));
        $filesToPublish = $publishableFilesMap->filter(fn (string $file): bool => in_array($file, $selectedFiles));

        $this->publishFiles($filesToPublish);

        return sprintf('Published files [%s]', $this->getPublishedFilesForOutput($filesToPublish));
    }

    /** @return \Symfony\Component\Finder\SplFileInfo[] */
    protected function findAllFilesForTag(): array
    {
        $paths = ServiceProvider::pathsToPublish(ViewServiceProvider::class, $this->group);
        $this->source = key($paths);
        $this->target = $paths[$this->source];

        return File::allFiles($this->source);
    }

    protected function mapPublishableFiles(array $search): Collection
    {
        return collect($search)->mapWithKeys(/** @return array<string, string> */ function (SplFileInfo $file): array {
            $targetPath = path_join($this->target, $file->getRelativePathname());

            return [Hyde::pathToRelative(realpath($file->getPathname())) => Hyde::pathToRelative($targetPath)];
        });
    }

    protected function promptForFiles(Collection $files, string $baseDir): array
    {
        $choices = $files->mapWithKeys(/** @return array<string, string> */ function (string $source) use ($baseDir): array {
            return [$source => Str::after($source, $baseDir.'/')];
        });

        return multiselect('Select the files you want to publish (CTRL+A to toggle all)', $choices, [], 10, 'required', hint: 'Navigate with arrow keys, space to select, enter to confirm.');
    }

    protected function publishFiles(Collection $selectedFiles): void
    {
        foreach ($selectedFiles as $source => $target) {
            Filesystem::ensureDirectoryExists(dirname($target));
            Filesystem::copy($source, $target); // Todo: See how we should handle existing files
        }
    }

    protected function getPublishedFilesForOutput(Collection $selectedFiles): string
    {
        return collect($selectedFiles)->map(fn (string $file): string => Str::after($file, basename($this->source).'/'))->implode(', ');
    }
}
