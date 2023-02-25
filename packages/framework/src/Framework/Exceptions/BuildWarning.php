<?php

declare(strict_types=1);

namespace Hyde\Framework\Exceptions;

use RuntimeException;

/**
 * @experimental Having a class like this extending an exception means it's easy to throw if enabled, however,
 *               it's not required for the build warnings feature and may be replaced by a simple array.
 */
class BuildWarning extends RuntimeException
{
    //
}
