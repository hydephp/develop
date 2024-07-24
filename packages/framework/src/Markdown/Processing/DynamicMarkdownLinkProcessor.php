<?php

declare(strict_types=1);

namespace Hyde\Markdown\Processing;

use Hyde\Hyde;
use Hyde\Support\Models\Route;
use Hyde\Framework\Exceptions\RouteNotFoundException;
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
            '/<a href="hyde::route\(([\'"]?)([^\'"]+)\1\)"/' => function (array $matches): string {
                $route = Hyde::route($matches[2]);

                static::validateRouteExists($route, $matches[2]);

                return '<a href="'.$route.'"';
            },
            '/<a href="hyde::relativeLink\(([\'"]?)([^\'"]+)\1\)"/' => function (array $matches): string {
                return '<a href="'.Hyde::relativeLink($matches[2]).'"';
            },
            '/<img src="hyde::asset\(([\'"]?)([^\'"]+)\1\)"/' => function (array $matches): string {
                return '<img src="'.Hyde::asset($matches[2]).'"';
            },
        ];
    }

    protected static function validateRouteExists(?Route $route, $routeKey): void
    {
        if ($route === null) {
            // While the other patterns work regardless of if input is valid,
            // this method returns null, which silently fails to an empty string.
            // So we instead throw an exception to alert the developer of the issue.
            throw new RouteNotFoundException($routeKey);
        }
    }
}
