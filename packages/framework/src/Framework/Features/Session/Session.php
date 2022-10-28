<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Session;

/**
 * Adds a simple session handler, arguably most useful to defer messages, for example,
 * to asynchronously add warnings to be used at a later point in the request lifecycle.
 *
 * It's bound into the service container as a singleton and is not persisted.
 * @example app(Session::class)->addWarning('warning');
 */
class Session
{
    protected array $warnings = [];

    public function addWarning(string $warning): void
    {
        $this->warnings[] = $warning;
    }

    public function getWarnings(): array
    {
        return $this->warnings;
    }
}
