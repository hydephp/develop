<?php

declare(strict_types=1);

namespace Hyde\Console\Helpers;

use Hyde\Facades\Filesystem;
use Hyde\Foundation\Providers\ViewServiceProvider;
use Hyde\Hyde;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

use function Hyde\path_join;

/**
 * @internal This class offloads logic from the PublishViewsCommand class and should not be used elsewhere.
 */
class InteractivePublishCommandHelper
{
    protected readonly string $group;
    protected readonly string $sourceDirectory;
    protected readonly string $targetDirectory;

    /** @var array<string, string> Map of source files to target files */
    protected readonly array $publishableFilesMap;

    /** @param "hyde-layouts"|"hyde-components"|"hyde-page-404" $group */
    public function __construct(string $group)
    {
        $this->group = $group;

        [$this->sourceDirectory, $this->targetDirectory] = $this->getPublishPaths();

        $this->publishableFilesMap = $this->mapPublishableFiles($this->findAllFilesForTag());
    }

    /** @return array<string, string> */
    public function getFileChoices(): array
    {
        return Arr::mapWithKeys($this->publishableFilesMap, /** @return array<string, string> */ function (string $source): array {
            return [$source => $this->pathRelativeToDirectory($source, $this->targetDirectory)];
        });
    }

    /** @param array<string> $selectedFiles */
    public function handle(array $selectedFiles): void
    {
        $filesToPublish = $this->filterPublishableFiles($selectedFiles);

        $this->publishFiles($filesToPublish);
    }

    /** @return array{string, string} */
    protected function getPublishPaths(): array
    {
        $viewPaths = ServiceProvider::pathsToPublish(ViewServiceProvider::class, $this->group);

        $source = array_key_first($viewPaths);
        $target = $viewPaths[$source];

        return [$source, $target];
    }

    /** @return \Symfony\Component\Finder\SplFileInfo[] */
    protected function findAllFilesForTag(): array
    {
        return File::allFiles($this->sourceDirectory);
    }

    /**
     * @param  \Symfony\Component\Finder\SplFileInfo[]  $search
     * @return array<string, string>
     */
    protected function mapPublishableFiles(array $search): array
    {
        return Arr::mapWithKeys($search, /** @return array<string, string> */ function (SplFileInfo $file): array {
            $targetPath = path_join($this->targetDirectory, $file->getRelativePathname());

            return [Hyde::pathToRelative(realpath($file->getPathname())) => Hyde::pathToRelative($targetPath)];
        });
    }

    /** @param array<string, string> $selectedFiles */
    protected function publishFiles(array $selectedFiles): void
    {
        foreach ($selectedFiles as $source => $target) {
            Filesystem::ensureDirectoryExists(dirname($target));
            Filesystem::copy($source, $target); // Todo: See how we should handle existing files
        }
    }

    /** @param array<string> $selectedFiles */
    public function formatOutput(array $selectedFiles): string
    {
        $publishedFiles = $this->mapPathsToRelativeDirectories($selectedFiles);

        return sprintf('Published %s [%s]',
            Str::plural('file', count($selectedFiles)),
            $publishedFiles
        );
    }

    protected function pathRelativeToDirectory(string $source, string $directory): string
    {
        return Str::after($source, basename($directory).'/');
    }

    /**
     * @param  array<string>  $selectedFiles
     * @return array<string, string>
     */
    protected function filterPublishableFiles(array $selectedFiles): array
    {
        return array_filter($this->publishableFilesMap, fn (string $file): bool => in_array($file, $selectedFiles));
    }

    /** @param array<string> $selectedFiles */
    protected function mapPathsToRelativeDirectories(array $selectedFiles): string
    {
        return collect($selectedFiles)
            ->map(fn (string $file): string => $this->pathRelativeToDirectory($file, $this->sourceDirectory))
            ->implode(', ');
    }
}
