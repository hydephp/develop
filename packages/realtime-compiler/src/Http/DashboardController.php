<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler\Http;

use Composer\InstalledVersions;
use Hyde\Framework\Actions\AnonymousViewCompiler;
use Hyde\Hyde;
use Hyde\RealtimeCompiler\Concerns\InteractsWithLaravel;

use Illuminate\Support\HtmlString;

use function app;
use function array_merge;
use function class_basename;
use function file_exists;

class DashboardController
{
    use InteractsWithLaravel;

    public string $title;

    public function __construct()
    {
        $this->bootApplication();

        $this->title = config('site.name') . ' - Dashboard';
    }

    public function show(): string
    {
        return (new AnonymousViewCompiler(__DIR__.'/../../resources/dashboard.blade.php', array_merge(
            (array) $this,
            ['controller' => $this],
        )))->__invoke();
    }

    public function getVersions(): array
    {
        return [
            'Git Version:' => app('git.version'),
            'Hyde Version:' => InstalledVersions::getPrettyVersion('hyde/hyde') ?: 'unreleased',
            'Framework Version:' => InstalledVersions::getPrettyVersion('hyde/framework') ?: 'unreleased',
        ];
    }

    /** @see \Hyde\Console\Commands\RouteListCommand */
    public function getPageList(): array
    {
        $routes = [];
        /** @var \Hyde\Support\Models\Route $route */
        foreach (Hyde::routes() as $route) {
            $routes[] = [
                'type' => [$route->getPageClass(), $this->formatPageType($route->getPageClass())],
                'route' => [$route->getLink(), $this->formatPageType($route->getRouteKey())],
                'source' => ([Hyde::path($route->getSourcePath()), $route->getSourcePath()]),
                'output' => ($this->formatOutputPath($route->getOutputPath())),
            ];
        }

        return $routes;
    }

    protected function formatPageType(string $class): string
    {
        return str_starts_with($class, 'Hyde') ? class_basename($class) : $class;
    }

    protected function formatOutputPath(string $path): array
    {
        if (! file_exists(Hyde::sitePath($path))) {
            return [null, "_site/$path"];
        }

        return [Hyde::sitePath($path), "_site/$path"];
    }
}
