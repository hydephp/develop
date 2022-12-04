<?php

declare(strict_types=1);

namespace Hyde\Framework\Concerns;

abstract class InvokableAction
{
    abstract public function __invoke();

    public static function call(...$args)
    {
        return (new static(...$args))->__invoke();
    }
}
