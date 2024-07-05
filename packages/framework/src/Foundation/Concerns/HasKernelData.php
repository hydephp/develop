<?php

declare(strict_types=1);

namespace Hyde\Foundation\Concerns;

use Illuminate\Support\Collection;

/**
 * Contains accessors and containers for data stored in the kernel.
 *
 * @internal Single-use trait for the HydeKernel class.
 *
 * @see \Hyde\Foundation\HydeKernel
 */
trait HasKernelData
{
    /**
     * The collection of authors defined in the config.
     *
     * @var \Illuminate\Support\Collection<string, \Hyde\Framework\Features\Blogging\Models\PostAuthor>
     */
    protected Collection $authors;
}
