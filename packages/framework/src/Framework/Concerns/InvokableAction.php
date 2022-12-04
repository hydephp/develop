<?php

declare(strict_types=1);

namespace Hyde\Framework\Concerns;

/**
 * Base class for invokable actions. Provides a helper to invoke the action statically.
 *
 * @see \Hyde\Framework\Testing\Feature\InvokableActionTest
 */
abstract class InvokableAction
{
    abstract public function __invoke();

    public static function call(...$args)
    {
        return (new static(...$args))->__invoke();
    }
}
