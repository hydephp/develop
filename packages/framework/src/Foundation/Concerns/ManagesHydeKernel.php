<?php
declare(strict_types=1);

namespace Hyde\Framework\Foundation\Concerns;

use Hyde\Framework\Foundation\FileCollection;
use Hyde\Framework\Foundation\PageCollection;
use Hyde\Framework\Foundation\RouteCollection;
use Hyde\Framework\HydeKernel;

/**
 * @internal Single-use trait for the HydeKernel class.
 *
 * @see \Hyde\Framework\HydeKernel
 */
trait ManagesHydeKernel
{
    public function boot(): void
    {
        $this->booted = true;

        $this->files = FileCollection::boot($this);
        $this->pages = PageCollection::boot($this);
        $this->routes = RouteCollection::boot($this);
    }

    public static function getInstance(): HydeKernel
    {
        return static::$instance;
    }

    public static function setInstance(HydeKernel $instance): void
    {
        static::$instance = $instance;
    }

    public function setBasePath(string $basePath): void
    {
        $this->basePath = rtrim($basePath, '/\\');
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }
}
