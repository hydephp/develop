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
use Illuminate\Support\Stringable;
use Symfony\Component\Finder\SplFileInfo;

use function Hyde\path_join;

/**
 * @internal This class offloads logic from the PublishViewsCommand class and should not be used elsewhere.
 */
class InteractivePublishCommandHelper
{
    /** @var array<string, string> Map of source files to target files */
    protected array $publishableFilesMap;

    /** @param array<string, string> $publishableFilesMap */
    public function __construct(array $publishableFilesMap)
    {
        $this->publishableFilesMap = $publishableFilesMap;
    }

    /** @return array<string, string> */
    public function getFileChoices(): array
    {
        $mostCommonDirectoryNominator = $this->getBaseDirectory();

        return Arr::mapWithKeys($this->publishableFilesMap, /** @return array<string, string> */ function (string $source) use ($mostCommonDirectoryNominator): array {
            return [$source => $this->pathRelativeToDirectory($source, $mostCommonDirectoryNominator)];
        });
    }

    protected function getBaseDirectory(): string
    {
        // Find the most specific common parent directory path for the files (in case they are in different directories, we want to trim as much as possible whilst keeping specificity and uniqueness)
        $partsMap = collect($this->publishableFilesMap)->map(fn (string $file): array => explode('/', $file));

        $commonParts = $partsMap->reduce(function (array $carry, array $parts): array {
            return array_intersect($carry, $parts);
        }, $partsMap->first());

        return implode('/', $commonParts);
    }

    public function publishFiles(): void
    {
        foreach ($this->publishableFilesMap as $source => $target) {
            if (! Filesystem::isFile(dirname($target))) {
                Filesystem::ensureDirectoryExists(dirname($target));
            } else {
                $target = dirname($target);
            }
            Filesystem::copy($source, $target); // Todo: See how we should handle existing files
        }
    }

    /**
     * @experimental This method may be toned down in the future.
     *
     * @param  array<string>  $selectedFiles
     */
    public function formatOutput(array $selectedFiles): string
    {
        $fileCount = count($selectedFiles);
        $displayLimit = 3;

        $fileNames = collect($selectedFiles)->map(fn (string $file): string => $this->pathRelativeToDirectory($file, $this->sourceDirectory));

        $displayFiles = $fileNames->take($displayLimit)->implode(', ');

        return Str::of('Published')
            ->when($fileCount === $this->publishableFilesMapCount(),
                fn (Stringable $str): Stringable => $str->append(' all files, including'),
                fn (Stringable $str): Stringable => $str->append(' ', Str::plural('file', $fileCount))
            )
            ->append(' [', $displayFiles, ']')
            ->when($fileCount > $displayLimit,
                fn (Stringable $str): Stringable => $str->append(' and ', $fileCount - $displayLimit, ' more')
            )
            ->toString();
    }

    protected function pathRelativeToDirectory(string $source, string $directory): string
    {
        return Str::after($source, basename($directory).'/');
    }

    protected function publishableFilesMapCount(): int
    {
        return count($this->publishableFilesMap);
    }
}
