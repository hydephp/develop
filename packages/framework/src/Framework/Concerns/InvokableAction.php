<?php

declare(strict_types=1);

namespace Hyde\Framework\Concerns;

use JetBrains\PhpStorm\Deprecated;

/**
 * Base class for invokable actions. Provides a helper to invoke the action statically.
 *
 * @deprecated None of these child classes are ever used invokably used.
 *             A better alternative is to simply use a static `handle` method as that offers full type and IDE support.
 * @see \Hyde\Framework\Testing\Feature\InvokableActionTest
 */
abstract class InvokableAction
{
    abstract public function __invoke(): mixed;

    #[Deprecated(replacement: '%class%::handle(%parametersList%)')]
    public static function call(mixed ...$args): mixed
    {
        return (new static(...$args))->__invoke();
    }
}
