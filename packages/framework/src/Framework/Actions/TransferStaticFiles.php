<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Hyde;
use Hyde\Facades\Config;
use Hyde\Facades\Filesystem;
use Hyde\Framework\Concerns\InteractsWithDirectories;
use Hyde\Framework\Exceptions\FileConflictException;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Support\Filesystem\MediaFile;
use Illuminate\Support\Collection;
use SplFileInfo;

use function collect;
use function strlen;
use function substr;

class TransferStaticFiles
{
    use InteractsWithDirectories;

    public static function handle(): void
    {
        $files = static::findStaticFiles()->map(function (SplFileInfo $file): array {
            $sourcePath = Hyde::pathToRelative($file->getPathname());

            return [
                'source' => $file->getPathname(),
                'output' => Hyde::sitePath(substr($sourcePath, strlen('_static/'))),
            ];
        });

        $currentBuildOutputPaths = static::currentBuildOutputPaths();

        $files->each(function (array $file) use ($currentBuildOutputPaths): void {
            if ($currentBuildOutputPaths->contains($file['output']) || Filesystem::isDirectory($file['output'])) {
                throw new FileConflictException($file['output']);
            }
        });

        $files->each(function (array $file): void {
            static::needsParentDirectory($file['output']);
            Filesystem::copy($file['source'], $file['output']);
        });
    }

    /** @return \Illuminate\Support\Collection<int, \SplFileInfo> */
    protected static function findStaticFiles(): Collection
    {
        if (! Filesystem::isDirectory('_static')) {
            return collect();
        }

        return collect(Filesystem::allFiles(Hyde::path('_static'), true));
    }

    /** @return \Illuminate\Support\Collection<int, string> */
    protected static function currentBuildOutputPaths(): Collection
    {
        $paths = Hyde::pages()->map(fn (HydePage $page): string => Hyde::sitePath($page->getOutputPath()))->values();

        if (Config::getBool('hyde.transfer_media_assets', true)) {
            $paths = $paths->concat(MediaFile::all()->map(fn (MediaFile $file): string => $file->getOutputPath()));
        }

        if (Config::getBool('hyde.generate_build_manifest', true)) {
            $paths->push(Hyde::path(Config::getString(
                'hyde.build_manifest_path',
                'app/storage/framework/cache/build-manifest.json'
            )));
        }

        return $paths;
    }
}
