<?php

declare(strict_types=1);

namespace Hyde\Foundation\Concerns;

use Hyde\Foundation\FileCollection;
use Hyde\Foundation\HydeKernel;
use Hyde\Foundation\PageCollection;
use Hyde\Foundation\RouteCollection;
use Hyde\Pages\Concerns\HydePage;

use function in_array;
use function is_subclass_of;

/**
 * @internal Single-use trait for the HydeKernel class.
 *
 * @see \Hyde\Foundation\HydeKernel
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

    public function setSourceRoot(string $sourceRoot): void
    {
        $this->sourceRoot = rtrim($sourceRoot, '/\\');
    }

    public function getSourceRoot(): string
    {
        return $this->sourceRoot;
    }

    /** @return array<class-string<\Hyde\Pages\Concerns\HydePage>> */
    public function getDiscoveredPageTypes(): array
    {
        return $this->pages()->map(function (HydePage $page): string {
            return $page::class;
        })->unique()->values()->toArray();
    }

    public function registerPageClass(string $pageClass): self
    {
        if ($this->booted) {
            throw new \BadMethodCallException('Cannot register a page class after the Kernel has been booted.');
        }

        if (! is_subclass_of($pageClass, HydePage::class)) {
            throw new \InvalidArgumentException('The specified class must be a subclass of HydePage.');
        }

        if (! in_array($pageClass, $this->pageClasses, true)) {
            $this->pageClasses[] = $pageClass;
        }

        return $this;
    }

    public function getRegisteredPageClasses(): array
    {
        return $this->pageClasses;
    }
}
