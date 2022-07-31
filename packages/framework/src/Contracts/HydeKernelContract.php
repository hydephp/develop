<?php

namespace Hyde\Framework\Contracts;

/**
 * The HydeKernel encapsulates a HydePHP project,
 * providing helpful methods for interacting with it.
 *
 * @see \Hyde\Framework\HydeKernel
 *
 * It is bound into the Laravel Application Service Container,
 * and can be accessed in a few ways.
 *
 * - Commonly, you'll use the Hyde facade:
 * @see \Hyde\Framework\Hyde (previosly this namespace contained the actual Kernel)
 *
 * @example \Hyde\Framework\Hyde::foo()
 *
 * - You can also use Dependency Injection to inject the Kernel into your own classes:
 * @example `__construct(HydeKernelContract $hyde)`
 *
 * - Or, you can use the hyde() function to get the Kernel:
 * @example `$hyde = hyde();
 *
 * - And finally, you can access the global `$hyde` variable defined in bootstrap.php:
 * @example `global $hyde; return $hyde;`
 */
interface HydeKernelContract
{
    public function getBasePath(): string;

    public function setBasePath(string $basePath);
}
