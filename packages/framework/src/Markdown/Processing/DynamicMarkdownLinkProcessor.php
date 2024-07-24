<?php

declare(strict_types=1);

namespace Hyde\Markdown\Processing;

use Hyde\Hyde;
use Hyde\Markdown\Contracts\MarkdownPostProcessorContract;

class DynamicMarkdownLinkProcessor implements MarkdownPostProcessorContract
{
    public static function postprocess(string $html): string
    {
        foreach (static::patterns() as $pattern => $replacement) {
            $html = preg_replace_callback($pattern, $replacement, $html);
        }

        return $html;
    }

    /** @return array<string, callable(array<int, string>): string> */
    protected static function patterns(): array
    {
        return [
            '/<a href="hyde::route\((\'|"|)([^\'"]+)\1\)"/' => function (array $matches): string {
                return '<a href="'.Hyde::route($matches[2]).'"';
            },
            '/<a href="hyde::relativeLink\((\'|"|)([^\'"]+)\1\)"/' => function (array $matches): string {
                return '<a href="'.Hyde::relativeLink($matches[2]).'"';
            },
            '/<img src="hyde::asset\((\'|"|)([^\'"]+)\1\)"/' => function (array $matches): string {
                return '<img src="'.Hyde::asset($matches[2]).'"';
            },
        ];
    }
}
