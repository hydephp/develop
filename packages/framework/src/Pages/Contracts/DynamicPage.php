<?php

declare(strict_types=1);

namespace Hyde\Pages\Contracts;

/**
 * This interface is used to mark page classes that are dynamically generated,
 * (i.e. not based on a source file), or that have dynamic path information.
 *
 * These page classes are excluded by the Hyde Auto Discovery process,
 * they must therefore be added to the HydeKernel by the developer.
 */
interface DynamicPage
{
    //
}
