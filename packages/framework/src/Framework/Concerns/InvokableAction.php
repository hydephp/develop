<?php

declare(strict_types=1);

namespace Hyde\Framework\Concerns;

abstract class InvokableAction
{
    abstract public function __invoke();
}
