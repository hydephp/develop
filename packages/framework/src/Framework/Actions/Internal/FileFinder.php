<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions\Internal;

use Hyde\Facades\Filesystem;
use Hyde\Hyde;
use Illuminate\Support\Collection;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

/**
 * @interal This class is used internally by the framework and is not part of the public API unless requested on GitHub.
 */
class FileFinder
{
    /**
     * @param  string|array<string>|false  $matchExtensions
     * @return \Illuminate\Support\Collection<int, string>
     */
    public static function handle(string $directory, string|array|false $matchExtensions = false, bool $recursive = false): Collection
    {
        if (! Filesystem::isDirectory($directory)) {
            return collect();
        }

        $finder = Finder::create()->files()->in(Hyde::path($directory));

        if ($recursive === false) {
            $finder->depth('== 0');
        }

        if ($matchExtensions !== false) {
            $finder->name(static::buildFileExtensionPattern($matchExtensions));
        }

        return collect($finder)->map(function (SplFileInfo $file): string {
            return Hyde::pathToRelative($file->getPathname());
        })->sort()->values();
    }

    /** @param string|array<string> $extensions */
    protected static function buildFileExtensionPattern(string|array $extensions): string
    {
        $extensions = (array) $extensions;

        // Normalize array by splitting any CSV strings within
        $extensions = array_merge(...array_map(function ($item) {
            return array_map('trim', explode(',', $item));
        }, $extensions));

        // Remove leading dots, escape extensions, and build the regex pattern
        return '/\.(' . implode('|', array_map(function (string $extension): string {
                return preg_quote(ltrim($extension, '.'), '/');
            }, $extensions)) . ')$/i';
    }
}
