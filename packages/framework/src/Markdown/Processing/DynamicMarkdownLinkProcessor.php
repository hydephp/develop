<?php

declare(strict_types=1);

namespace Hyde\Markdown\Processing;

use Hyde\Hyde;
use Hyde\Markdown\Contracts\MarkdownPostProcessorContract;

class DynamicMarkdownLinkProcessor implements MarkdownPostProcessorContract
{
    public static function postprocess(string $html): string
    {
        /** @var array<string, callable(array<int, string>): string> $patterns */
        $patterns = [
            '/<a href="hyde::route\(\'([^\']+)\'\)"/' => function (array $matches): string {
                return '<a href="'.Hyde::route($matches[1]).'"';
            },
            '/<a href="hyde::relativeLink\(\'([^\']+)\'\)"/' => function (array $matches): string {
                return '<a href="'.Hyde::relativeLink($matches[1]).'"';
            },
            '/<img src="hyde::asset\(\'([^\']+)\'\)"/' => function (array $matches): string {
                return '<img src="'.Hyde::asset($matches[1]).'"';
            },
        ];

        foreach ($patterns as $pattern => $replacement) {
            $html = preg_replace_callback($pattern, $replacement, $html);
        }

        return $html;
    }
}
