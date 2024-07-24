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
        // Todo cache in static property bound to kernel version (but evaluation is tied to rendering page)

        $map = [];

        /** @var \Hyde\Support\Models\Route $route */
        foreach (Hyde::routes() as $route) {
            $map[$route->getSourcePath()] = $route;
        }

        foreach ($map as $sourcePath => $route) {
            $patterns = [
                '<a href="'.$sourcePath.'">',
                '<a href="/'.$sourcePath.'">',
            ];

            $html = str_replace($patterns, '<a href="'.$route->getLink().'">', $html);
        }

        // maybe we just need to cache this, as the kernel is already a singleton, but this is not
        $assetMap = [];

        foreach (MediaFile::all() as $mediaFile) {
            $assetMap[$mediaFile->getPath()] = $mediaFile;
        }

        foreach ($assetMap as $path => $mediaFile) {
            $patterns = [
                '<img src="'.$path.'"',
                '<img src="/'.$path.'"',
            ];

            $localPath = Str::after($mediaFile->getPath(), '_media/');
            $html = str_replace($patterns, '<img src="'.Hyde::asset($localPath).'"', $html);
        }

        return $html;
    }
}
