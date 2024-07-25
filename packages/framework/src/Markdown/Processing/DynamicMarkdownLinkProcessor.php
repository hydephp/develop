<?php

declare(strict_types=1);

namespace Hyde\Markdown\Processing;

use Hyde\Hyde;
use Hyde\Markdown\Contracts\MarkdownPostProcessorContract;

class DynamicMarkdownLinkProcessor implements MarkdownPostProcessorContract
{
    public static function postprocess(string $html): string
    {
        foreach (static::routeMap() as $sourcePath => $route) {
            $patterns = [
                '<a href="'.$sourcePath.'">',
                '<a href="/'.$sourcePath.'">',
            ];

            $html = str_replace($patterns, '<a href="'.$route->getLink().'">', $html);
        }

        foreach (static::assetMap() as $path => $mediaFile) {
            $patterns = [
                '<img src="'.$path.'"',
                '<img src="/'.$path.'"',
            ];

            $html = str_replace($patterns, '<img src="'.static::assetPath($mediaFile).'"', $html);
        }

        return $html;
    }
}
