<?php

declare(strict_types=1);

namespace Hyde\Foundation\Concerns;

use BadMethodCallException;
use Hyde\Foundation\FileCollection;
use Hyde\Foundation\HydeKernel;
use Hyde\Foundation\PageCollection;
use Hyde\Foundation\RouteCollection;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Pages\Contracts\DynamicPage;
use function in_array;
use InvalidArgumentException;
use function is_subclass_of;

/**
 * @internal Single-use trait for the HydeKernel class.
 *
 * @see \Hyde\Foundation\HydeKernel
 */
trait ManagesHydeKernel
{
    /**
     * @var bool Is the Kernel currently booting?
     *
     * @deprecated As I don't think it's solving the right problem.
     */
    protected bool $booting = false;

    /** @var bool Is the Kernel ready to be booted? */
    protected bool $readyToBoot = false;

    public function isBooted(): bool
    {
        return $this->booted;
    }

    public function canBoot(): bool
    {
        return $this->readyToBoot && ! $this->booting;
    }

    /** @internal */
    public function readyToBoot(): void
    {
        $this->readyToBoot = true;
    }

    public function boot(): void
    {
        if ($this->isBooted()) {
            return;
        }

        if (! $this->readyToBoot) {
            throw new BadMethodCallException('The HydeKernel cannot be booted yet.');
        }

        if ($this->booting) {
            throw new BadMethodCallException('The HydeKernel is already booting.');
        }

        $this->booting = true;

        $this->files = FileCollection::boot($this);
        $this->pages = PageCollection::boot($this);
        $this->routes = RouteCollection::boot($this);

        $this->booting = false;
        $this->booted = true;
    }

    /** @internal Reboot the kernel - useful for resetting the application during testing */
    public static function reboot(): void
    {
        $kernel = static::getInstance();

        $kernel->files = FileCollection::boot($kernel);
        $kernel->pages = PageCollection::boot($kernel);
        $kernel->routes = RouteCollection::boot($kernel);
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

    /**
     * Developer Information.
     *
     * @experimental This feature is experimental and may change substantially before the 1.0.0 release.
     *
     * If you are a package developer, and want a custom page class to be discovered,
     * you'll need to register it sometime before the boot process, before discovery is run.
     * Typically, you would do this by calling this method in the register method of a service provider.
     * Hyde will then automatically discover source files for the new page class, and compile them during the build process.
     *
     * @param  class-string<\Hyde\Pages\Concerns\HydePage>  $pageClass
     */
    public function registerPageClass(string $pageClass): void
    {
        if ($this->isBooted()) {
            // We throw an exception here to prevent the developer from registering a page class after the Kernel has been booted.
            // The reason we do this is because at this point all the source files have already been discovered and parsed.
            // If we allowed new classes after this point, we would have to reboot everything which adds complexity.

            throw new BadMethodCallException('Cannot register a page class after the Kernel has been booted.');
        }

        if (! is_subclass_of($pageClass, HydePage::class)) {
            throw new InvalidArgumentException('The specified class must be a subclass of HydePage.');
        }

        if (is_subclass_of($pageClass, DynamicPage::class)) {
            throw new InvalidArgumentException('The specified class must not be a subclass of DynamicPage.');
        }

        if (! in_array($pageClass, $this->pageClasses, true)) {
            $this->pageClasses[] = $pageClass;
        }
    }

    /** @return array<class-string<\Hyde\Pages\Concerns\HydePage>> */
    public function getRegisteredPageClasses(): array
    {
        return $this->pageClasses;
    }
}
