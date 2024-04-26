<?php

declare(strict_types=1);

namespace Hyde\Support\Internal;

use Closure;

class DeferredOption
{
    protected Closure $closure;

    public function __construct(Closure $closure)
    {
        $this->closure = $closure;
    }

    public function __invoke(): mixed
    {
        return $this->closure->__invoke();
    }
}
