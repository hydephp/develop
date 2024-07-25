<?php

declare(strict_types=1);

namespace Hyde\Markdown\Processing;

use Hyde\Hyde;
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
        $assetMap = [];

        foreach (MediaFile::all() as $mediaFile) {
            $assetMap[$mediaFile->getPath()] = $mediaFile;
        }

        return $assetMap;
    }
}
