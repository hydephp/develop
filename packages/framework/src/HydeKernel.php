<?php

namespace Hyde\Framework;

use Composer\InstalledVersions;
use Hyde\Framework\Concerns\JsonSerializesArrayable;
use Hyde\Framework\Contracts\HydeKernelContract;
use Hyde\Framework\Contracts\RouteContract;
use Hyde\Framework\Foundation\FileCollection;
use Hyde\Framework\Foundation\Filesystem;
use Hyde\Framework\Foundation\Hyperlinks;
use Hyde\Framework\Foundation\PageCollection;
use Hyde\Framework\Foundation\RouteCollection;
use Hyde\Framework\Helpers\Features;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Traits\Macroable;

/**
 * Encapsulates a HydePHP project, providing helpful methods for interacting with it.
 *
 * @see \Hyde\Framework\Hyde for the facade commonly used to access this class.
 *
 * @author  Caen De Silva <caen@desilva.se>
 * @copyright 2022 Caen De Silva
 * @license MIT License
 *
 * @link https://hydephp.com/
 */
class HydeKernel implements HydeKernelContract, Arrayable, \JsonSerializable
{
    use Macroable;
    use JsonSerializesArrayable;

    use Foundation\Concerns\ImplementsStringHelpers;
    use Foundation\Concerns\ForwardsHyperlinks;

    protected static HydeKernel $instance;

    protected string $basePath;

    protected Filesystem $filesystem;
    protected Hyperlinks $hyperlinks;

    protected FileCollection $files;
    protected PageCollection $pages;
    protected RouteCollection $routes;

    protected bool $booted = false;

    public function __construct(?string $basePath = null)
    {
        $this->setBasePath($basePath ?? getcwd());
        $this->filesystem = new Filesystem($this);
        $this->hyperlinks = new Hyperlinks($this);
    }

    public function boot(): void
    {
        $this->booted = true;

        $this->files = FileCollection::boot($this);
        $this->pages = PageCollection::boot($this);
        $this->routes = RouteCollection::boot($this);
    }

    public static function setInstance(HydeKernel $instance): void
    {
        static::$instance = $instance;
    }

    public static function getInstance(): HydeKernel
    {
        return static::$instance;
    }

    public static function version(): string
    {
        return InstalledVersions::getPrettyVersion('hyde/framework') ?: 'unreleased';
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function setBasePath(string $basePath): void
    {
        $this->basePath = rtrim($basePath, '/\\');
    }

    public function features(): Features
    {
        return new Features;
    }

    public function hasFeature(string $feature): bool
    {
        return Features::enabled($feature);
    }

    public function currentPage(): string
    {
        return View::shared('currentPage', '');
    }

    public function currentRoute(): ?RouteContract
    {
        return View::shared('currentRoute');
    }

    public function files(): FileCollection
    {
        if (! $this->booted) {
            $this->boot();
        }

        return $this->files;
    }

    public function pages(): PageCollection
    {
        if (! $this->booted) {
            $this->boot();
        }

        return $this->pages;
    }

    public function routes(): RouteCollection
    {
        if (! $this->booted) {
            $this->boot();
        }

        return $this->routes;
    }

    public function path(string $path = ''): string
    {
        return $this->filesystem->path($path);
    }

    public function vendorPath(string $path = ''): string
    {
        return $this->filesystem->vendorPath($path);
    }

    public function copy(string $from, string $to): bool
    {
        return $this->filesystem->copy($from, $to);
    }

    public function touch(string|array $path): bool
    {
        return $this->filesystem->touch($path);
    }

    public function unlink(string|array $path): bool
    {
        return $this->filesystem->unlink($path);
    }

    public function getModelSourcePath(string $model, string $path = ''): string
    {
        return $this->filesystem->getModelSourcePath($model, $path);
    }

    public function getBladePagePath(string $path = ''): string
    {
        return $this->filesystem->getBladePagePath($path);
    }

    public function getMarkdownPagePath(string $path = ''): string
    {
        return $this->filesystem->getMarkdownPagePath($path);
    }

    public function getMarkdownPostPath(string $path = ''): string
    {
        return $this->filesystem->getMarkdownPostPath($path);
    }

    public function getDocumentationPagePath(string $path = ''): string
    {
        return $this->filesystem->getDocumentationPagePath($path);
    }

    public function getSiteOutputPath(string $path = ''): string
    {
        return $this->filesystem->getSiteOutputPath($path);
    }

    public function pathToRelative(string $path): string
    {
        return $this->filesystem->pathToRelative($path);
    }

    /**
     * @inheritDoc
     *
     * @return array{basePath: string, features: \Hyde\Framework\Helpers\Features, pages: \Hyde\Framework\Foundation\PageCollection, routes: \Hyde\Framework\Foundation\RouteCollection}
     */
    public function toArray(): array
    {
        return [
            'basePath' => $this->basePath,
            'features' => $this->features(),
            'files' => $this->files(),
            'pages' => $this->pages(),
            'routes' => $this->routes(),
        ];
    }
}
