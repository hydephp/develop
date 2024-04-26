<?php

declare(strict_types=1);

namespace Hyde\Support\Internal;

use Closure;
use Illuminate\Config\Repository;

class DeferredOption
{
    protected Closure $closure;

    public function __construct(Closure $closure)
    {
        $this->closure = $closure;
    }

    public function __invoke(Repository $config): mixed
    {
        return $this->closure->__invoke($config);
    }
}
