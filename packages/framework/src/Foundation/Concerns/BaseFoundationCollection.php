<?php

declare(strict_types=1);

namespace Hyde\Foundation\Concerns;

use Hyde\Foundation\HydeKernel;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use RuntimeException;
use Throwable;

/**
 * Base class for the kernel auto-discovery collections.
 *
 * These collections are the heart of the discovery process.
 *
 * They are responsible for discovering the files, pages, and routes,
 * for the project, and also act as containers for the discovered data.
 *
 * The collections are stored as singletons in the kernel, and can be
 * accessed via the kernel's `getFiles()`, `getPages()`, and `getRoutes()`
 * methods respectively, or through the corresponding facade helper classes.
 *
 * Each collection depends on the earlier one, thus they are booted in sequence.
 *
 * @see \Hyde\Foundation\Kernel\FileCollection Discovers the source files in the project.
 * @see \Hyde\Foundation\Kernel\PageCollection Parses the source files into page objects.
 * @see \Hyde\Foundation\Kernel\RouteCollection Creates route objects from the page objects.
 *
 * The collections are constructed and booted in the kernel through the `BootsHydeKernel` trait.
 * Between the construction and booting is a time-frame where the extensions can hook into
 * the discovery process and add their data through the `runExtensionHandlers` API.
 */
abstract class BaseFoundationCollection extends Collection
{
    protected HydeKernel $kernel;

    abstract protected function runDiscovery(): void;

    abstract protected function runExtensionHandlers(): void;

    public static function init(HydeKernel $kernel): static
    {
        return (new static())->setKernel($kernel);
    }

    final public function boot(): static
    {
        try {
            $this->runDiscovery();
            $this->runExtensionHandlers();
        } catch (Throwable $exception) {
            throw new RuntimeException("An error occurred during the discovery process: {$exception->getMessage()}", previous: $exception);
        }

        return $this;
    }

    final protected function __construct(array|Arrayable|null $items = [])
    {
        parent::__construct($items);
    }

    /** @return $this */
    protected function setKernel(HydeKernel $kernel): static
    {
        $this->kernel = $kernel;

        return $this;
    }
}
