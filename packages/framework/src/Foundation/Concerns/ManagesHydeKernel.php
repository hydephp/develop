<?php

declare(strict_types=1);

namespace Hyde\Foundation\Concerns;

use BadMethodCallException;
use Hyde\Foundation\HydeKernel;
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
    public static function getInstance(): HydeKernel
    {
        return static::$instance;
    }

    public static function setInstance(HydeKernel $instance): void
    {
        static::$instance = $instance;
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function setBasePath(string $basePath): void
    {
        $this->basePath = rtrim($basePath, '/\\');
    }

    public function getSourceRoot(): string
    {
        return $this->sourceRoot;
    }

    public function setSourceRoot(string $sourceRoot): void
    {
        $this->sourceRoot = $this->normalizeSourcePath($sourceRoot);
    }

    public function getOutputDirectory(): string
    {
        return $this->outputDirectory;
    }

    public function setOutputDirectory(string $outputDirectory): void
    {
        $this->outputDirectory = $this->normalizeSourcePath($outputDirectory);
    }

    public function getMediaDirectory(): string
    {
        return $this->mediaDirectory;
    }

    public function setMediaDirectory(string $mediaDirectory): void
    {
        $this->mediaDirectory = $this->normalizeSourcePath($mediaDirectory);
    }

    public function getMediaOutputDirectory(): string
    {
        return ltrim($this->getMediaDirectory(), '_');
    }

    /**
     * @deprecated
     * @internal This method is not part of the public API and should not be used outside the HydePHP framework.
     */
    public function registerPageClass(string $pageClass): void
    {
        if (! in_array($pageClass, $this->pageClasses, true)) {
            $this->pageClasses[] = $pageClass;
        }
    }

    /**
     * @deprecated
     * @internal
     * @return array<class-string<\Hyde\Pages\Concerns\HydePage>>
     */
    public function getRegisteredPageClasses(): array
    {
        return $this->pageClasses;
    }

    /**
     * Register a HydePHP extension within the HydeKernel.
     *
     * Typically, you would call this method in the register method of a service provider.
     * If your package uses the standard Laravel (Composer) package discovery feature,
     * the extension will automatically be enabled when the package is installed.
     *
     * @param  class-string<\Hyde\Foundation\Concerns\HydeExtension>  $extension
     */
    public function registerExtension(string $extension): void
    {
        if ($this->booted) {
            // We throw an exception here to prevent the developer from registering aa extension after the Kernel has been booted.
            // The reason we do this is because at this point all the source files have already been discovered and parsed.
            // If we allowed new classes after this point, we would have to reboot everything which adds complexity.

            throw new BadMethodCallException('Cannot register an extension after the Kernel has been booted.');
        }

        if (! is_subclass_of($extension, HydeExtension::class)) {
            // We want to make sure that the extension class extends the HydeExtension class,
            // so that we won't have to check the methods we need to call exist later on.

            throw new InvalidArgumentException('The specified class must extend the HydeExtension class.');
        }

        if (! in_array($extension, $this->extensions, true)) {
            $this->extensions[] = $extension;
        }
    }

    /** @return array<class-string<\Hyde\Foundation\Concerns\HydeExtension>> */
    public function getRegisteredExtensions(): array
    {
        return $this->extensions;
    }

    protected function normalizeSourcePath(string $outputDirectory): string
    {
        return $this->pathToRelative(rtrim($outputDirectory, '/\\'));
    }
}
