<?php

declare(strict_types=1);

namespace Hyde\Console\Helpers;

use Hyde\Facades\Filesystem;
use Hyde\Foundation\Providers\ViewServiceProvider;
use Hyde\Hyde;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
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

    public function __construct(string $group)
    {
        $this->group = $group;

        [$this->sourceDirectory, $this->targetDirectory] = $this->getPublishPaths();

        $filesForTag = $this->findAllFilesForTag();
        $this->publishableFilesMap = $this->mapPublishableFiles($filesForTag);
    }

    public function getFileChoices(): array
    {
        return Arr::mapWithKeys($this->publishableFilesMap, /** @return array<string, string> */ function (string $source): array {
            return [$source => $this->pathRelativeToDirectory($source, $this->targetDirectory)];
        });
    }

    public function handle(array $selectedFiles): string
    {
        $filesToPublish = array_filter($this->publishableFilesMap, fn (string $file): bool => in_array($file, $selectedFiles));

        $this->publishFiles($filesToPublish);

        return sprintf('Published files [%s]', $this->getPublishedFilesForOutput($filesToPublish));
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

    protected function mapPublishableFiles(array $search): array
    {
        return Arr::mapWithKeys($search, /** @return array<string, string> */ function (SplFileInfo $file): array {
            $targetPath = path_join($this->targetDirectory, $file->getRelativePathname());

            return [Hyde::pathToRelative(realpath($file->getPathname())) => Hyde::pathToRelative($targetPath)];
        });
    }

    protected function publishFiles(array $selectedFiles): void
    {
        foreach ($selectedFiles as $source => $target) {
            Filesystem::ensureDirectoryExists(dirname($target));
            Filesystem::copy($source, $target); // Todo: See how we should handle existing files
        }
    }

    protected function getPublishedFilesForOutput(array $selectedFiles): string
    {
        return collect($selectedFiles)->map(fn (string $file): string => $this->pathRelativeToDirectory($file, $this->sourceDirectory))->implode(', ');
    }

    protected function pathRelativeToDirectory(string $source, string $directory): string
    {
        return Str::after($source, basename($directory).'/');
    }
}
