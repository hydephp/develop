<?php

declare(strict_types=1);

namespace Hyde\Foundation\Concerns;

use BadMethodCallException;
use InvalidArgumentException;
use function array_map;
use function array_merge;
use function array_unique;
use function in_array;
use function is_subclass_of;

/**
 * @internal Single-use trait for the HydeKernel class.
 *
 * @see \Hyde\Foundation\HydeKernel
 */
trait ManagesExtensions
{
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

    /** @return array<class-string<\Hyde\Pages\Concerns\HydePage>> */
    public function getRegisteredPageClasses(): array
    {
        return array_unique(array_merge(...array_map(function (string $extension): array {
            /** @var <class-string<\Hyde\Foundation\Concerns\HydeExtension>> $extension */
            return $extension::getPageClasses();
        }, $this->getRegisteredExtensions())));
    }
}
