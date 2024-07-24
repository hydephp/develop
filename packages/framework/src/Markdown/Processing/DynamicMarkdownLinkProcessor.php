<?php

declare(strict_types=1);

namespace Hyde\Markdown\Processing;

use Hyde\Hyde;
use Hyde\Facades\Filesystem;
use Hyde\Support\Models\Route;
use Hyde\Support\Facades\Render;
use Illuminate\Support\Facades\File;
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

            $exception = new RouteNotFoundException($routeKey);

            // In order to show the developer where this error is, we try to find the faulty Markdown file.
            self::tryToFindErroredLine($routeKey, $exception);

            throw $exception;
        }
    }

    /**
     * @interal If we use more features like these we may want to wrap the RouteNotFoundException in a custom MarkdownPageException.
     *
     * @experimental
     */
    protected static function tryToFindErroredLine($routeKey, RouteNotFoundException $exception): void
    {
        $page = Render::getPage();
        if ($page !== null && Filesystem::exists($page->getSourcePath())) {
            $path = $page->getSourcePath();
            $contents = Filesystem::getContents($path);
            // Try to find the line number of the error.
            $lineNumber = strpos($contents, $routeKey);
            if ($lineNumber !== false) {
                $lineNumber = substr_count(substr($contents, 0, $lineNumber), "\n") + 1;
                $exception->setErroredFile($path, $lineNumber);
            } else {
                $exception->setErroredFile($path);
            }
        }
    }
}
