<?php

declare(strict_types=1);

namespace Hyde\Framework\Factories\Concerns;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Streamlines the data construction specific to a page.
 *
 * Simply pass along the data the class needs to run, then access the data using the toArray() method.
 */
abstract class PageFactory implements Arrayable
{
    abstract public function toArray(): array;
}
