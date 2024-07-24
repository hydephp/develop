<?php

declare(strict_types=1);

namespace Hyde\Markdown\Processing;

use Hyde\Hyde;
use Illuminate\Support\Str;
use Hyde\Support\Filesystem\MediaFile;
use Hyde\Markdown\Contracts\MarkdownPostProcessorContract;

class DynamicMarkdownLinkProcessor implements MarkdownPostProcessorContract
{
    public static function postprocess(string $html): string
    {
        foreach (static::routeMap() as $sourcePath => $route) {
            $patterns = [
                sprintf('<a href="%s">', $sourcePath),
                sprintf('<a href="/%s">', $sourcePath),
            ];

            $html = str_replace($patterns, sprintf('<a href="%s">', $route->getLink()), $html);
        }

        foreach (static::assetMap() as $path => $mediaFile) {
            $patterns = [
                sprintf('<img src="%s"', $path),
                sprintf('<img src="/%s"', $path),
            ];

            $html = str_replace($patterns, sprintf('<img src="%s"', static::assetPath($mediaFile)), $html);
        }

        return $html;
    }

    /**
     * @return array<string, \Hyde\Support\Models\Route>
     */
    protected static function routeMap(): array
    {
        // Todo cache in static property bound to kernel version (but evaluation is tied to rendering page)

        $map = [];

        /** @var \Hyde\Support\Models\Route $route */
        foreach (Hyde::routes() as $route) {
            $map[$route->getSourcePath()] = $route;
        }

        return $map;
    }

    /**
     * @return array<string, \Hyde\Support\Filesystem\MediaFile>
     */
    protected static function assetMap(): array
    {
        // maybe we just need to cache this, as the kernel is already a singleton, but this is not
        $assetMap = [];

        foreach (MediaFile::all() as $mediaFile) {
            $assetMap[$mediaFile->getPath()] = $mediaFile;
        }

        return $assetMap;
    }

    protected static function assetPath(MediaFile $mediaFile): string
    {
        return Hyde::asset(Str::after($mediaFile->getPath(), '_media/'));
    }
}
