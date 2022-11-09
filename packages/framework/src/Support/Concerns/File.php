<?php

declare(strict_types=1);

namespace Hyde\Support\Concerns;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;
use Stringable;

/**
 * Filesystem abstraction for a file stored in the project.
 *
 * @see \Hyde\Framework\Testing\Feature\FileTest
 */
abstract class File implements Arrayable, JsonSerializable, Stringable
{
    //
}
