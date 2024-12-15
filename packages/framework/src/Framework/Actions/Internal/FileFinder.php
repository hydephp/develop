<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions\Internal;

use Hyde\Facades\Filesystem;
use Hyde\Hyde;
use Illuminate\Support\Collection;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

/**
 * @interal This class is used internally by the framework and is not part of the public API, unless that is requested on GitHub with a valid use case.
 */
class FileFinder
{
    /**
     * @param  array<string>|string|false  $matchExtensions
     * @return \Illuminate\Support\Collection<int, string>
     */
    public static function handle(string $directory, array|string|false $matchExtensions = false, bool $recursive = false): Collection
    {
        if (! Filesystem::isDirectory($directory)) {
            return collect();
        }

        $finder = Finder::create()->files()->in(Hyde::path($directory));

        if ($recursive === false) {
            $finder->depth('== 0');
        }

        if ($matchExtensions !== false) {
            $finder->name(static::buildFileExtensionPattern((array) $matchExtensions));
        }

        return collect($finder)->map(function (SplFileInfo $file): string {
            return Hyde::pathToRelative($file->getPathname());
        })->sort()->values();
    }

    /** @param array<string> $extensions */
    protected static function buildFileExtensionPattern(array $extensions): string
    {
        $extensions = self::expandCommaSeparatedValues($extensions);

        return '/\.('.self::normalizeExtensionForRegexPattern($extensions).')$/i';
    }

    /** @param array<string> $extensions */
    private static function expandCommaSeparatedValues(array $extensions): array
    {
        return array_merge(...array_map(function (string $item): array {
            return array_map(fn (string $item): string => trim($item), explode(',', $item));
        }, $extensions));
    }

    /** @param array<string> $extensions */
    private static function normalizeExtensionForRegexPattern(array $extensions): string
    {
        return implode('|', array_map(function (string $extension): string {
            return preg_quote(ltrim($extension, '.'), '/');
        }, $extensions));
    }
}
