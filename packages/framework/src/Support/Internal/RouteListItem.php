<?php

declare(strict_types=1);

namespace Hyde\Support\Internal;

use Hyde\Hyde;
use Hyde\Pages\InMemoryPage;
use Hyde\Support\Models\Route;
use Hyde\Console\Concerns\Command;

use function filled;
use function sprintf;
use function file_exists;
use function class_basename;
use function str_starts_with;

/**
 * @internal This class is internal and should not be depended on outside the HydePHP framework code.
 */
class RouteListItem
{
    protected Route $route;

    public static function format(Route $route): array
    {
        $item = new static($route);

        return [
            'page_type' => $item->stylePageType($route->getPageClass()),
            'source_file' => $item->styleSourcePath($route->getSourcePath()),
            'output_file' => $item->styleOutputPath($route->getOutputPath()),
            'route_key' => $item->styleRouteKey($route->getRouteKey()),
        ];
    }

    protected function __construct(Route $route)
    {
        $this->route = $route;
    }

    protected function stylePageType(string $class): string
    {
        $type = $this->getPageType($class);

        $page = $this->route->getPage();

        if ($page instanceof InMemoryPage && $page->hasMacro('typeLabel')) {
            $type .= sprintf(' <fg=gray>(%s)</>', (string) $page->__call('typeLabel', []));
        }

        return $type;
    }

    protected function styleSourcePath(string $path): string
    {
        if ($this->getSourcePath($path) === 'none') {
            return '<fg=gray>none</>';
        }

        return file_exists(Hyde::path($path))
            ? $this->href(Command::fileLink(Hyde::path($path)), $this->getSourcePath($path))
            : $this->getSourcePath($path);
    }

    protected function styleOutputPath(string $path): string
    {
        return file_exists(Hyde::sitePath($path))
            ? $this->href(Command::fileLink(Hyde::sitePath($path)), $this->getOutputPath($path))
            : $this->getOutputPath($path);
    }

    protected function styleRouteKey(string $key): string
    {
        return $key;
    }

    protected function getPageType(string $class): string
    {
        return str_starts_with($class, 'Hyde') ? class_basename($class) : $class;
    }

    protected function getSourcePath(string $path): string
    {
        return $this->isPageDiscoverable() ? $path : 'none';
    }

    protected function getOutputPath(string $path): string
    {
        return Hyde::getOutputDirectory()."/$path";
    }

    protected function href(string $link, string $label): string
    {
        return "<href=$link>$label</>";
    }

    protected function isPageDiscoverable(): bool
    {
        return filled($this->route->getSourcePath()) && ! $this->route->getPage() instanceof InMemoryPage;
    }
}
