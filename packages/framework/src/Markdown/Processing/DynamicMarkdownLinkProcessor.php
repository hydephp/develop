<?php

declare(strict_types=1);

namespace Hyde\Markdown\Processing;

use Hyde\Hyde;
use Illuminate\Support\Str;
use Hyde\Support\Models\Route;
use Hyde\Support\Filesystem\MediaFile;
use Hyde\Markdown\Contracts\MarkdownPostProcessorContract;

class DynamicMarkdownLinkProcessor implements MarkdownPostProcessorContract
{
    /** @var array<string, \Hyde\Support\Filesystem\MediaFile>|null */
    protected static ?array $assetMapCache = null;

    public static function postprocess(string $html): string
    {
        $html = static::processMap(static::routeMap(), $html, 'a', 'href');
        $html = static::processMap(static::assetMap(), $html, 'img', 'src');

        return $html;
    }

    /**
     * @param array<string, \Hyde\Support\Models\Route|\Hyde\Support\Filesystem\MediaFile> $map
     */
    protected static function processMap(array $map, string $html, string $tag, string $attribute): string
    {
        foreach ($map as $sourcePath => $item) {
            $patterns = [
                sprintf('<%s %s="%s"', $tag, $attribute, $sourcePath),
                sprintf('<%s %s="/%s"', $tag, $attribute, $sourcePath),
            ];

            $replacement = sprintf('<%s %s="%s"', $tag, $attribute, static::getItemPath($item));
            $html = str_replace($patterns, $replacement, $html);
        }

        return $html;
    }

    protected static function getItemPath(MediaFile|Route $item): string
    {
        return $item instanceof MediaFile ? static::assetPath($item) : $item->getLink();
    }

    /** @return array<string, \Hyde\Support\Models\Route> */
    protected static function routeMap(): array
    {
        $map = [];

        /** @var \Hyde\Support\Models\Route $route */
        foreach (Hyde::routes() as $route) {
            $map[$route->getSourcePath()] = $route;
        }

        return $map;
    }

    /** @return array<string, \Hyde\Support\Filesystem\MediaFile> */
    protected static function assetMap(): array
    {
        if (static::$assetMapCache === null) {
            static::$assetMapCache = [];

            foreach (MediaFile::all() as $mediaFile) {
                static::$assetMapCache[$mediaFile->getPath()] = $mediaFile;
            }
        }

        return static::$assetMapCache;
    }

    protected static function assetPath(MediaFile $mediaFile): string
    {
        return Hyde::asset(Str::after($mediaFile->getPath(), '_media/'));
    }

    /** @internal Testing helper to reset the asset map cache. */
    public static function resetAssetMapCache(): void
    {
        static::$assetMapCache = null;
    }
}
