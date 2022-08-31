<?php

namespace Hyde\Framework\Contracts;

use Hyde\Framework\HydeKernel;

/**
 * The HydeKernel encapsulates a HydePHP project,
 * providing helpful methods for interacting with it.
 *
 * @deprecated v0.61.0-beta - Type hint the HydeKernel::class instead
 * @see \Hyde\Framework\HydeKernel
 *
 * It is stored as a singleton in the HydeKernel class, and is bound into the
 * Laravel Application Service Container, and can be accessed in a few ways.
 *
 * - Commonly, you'll use the Hyde facade:
 * @see \Hyde\Framework\Hyde (previosly this namespace contained the actual Kernel)
 *
 * @example \Hyde\Framework\Hyde::foo()
 *
 * - You can also use Dependency Injection to inject the Kernel into your own classes:
 * @example `__construct(HydeKernel $hyde)`
 *
 * - Or, you can use the hyde() function to get the Kernel:
 * @example `$hyde = hyde();
 *
 * - And finally, you can access the global `$hyde` variable defined in bootstrap.php:
 * @example `global $hyde; return $hyde;`
 */
interface HydeKernelContract
{
    public function boot(): void;

    public static function setInstance(HydeKernel $instance): void;

    public static function getInstance(): HydeKernel;

    public function getBasePath(): string;

    public function setBasePath(string $basePath);
}
