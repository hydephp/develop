<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Session;

use function array_key_exists;

/**
 * Adds a simple session handler, arguably most useful to defer messages, for example,
 * to asynchronously add warnings to be used at a later point in the request lifecycle.
 *
 * @internal This class is currently experimental and should not be relied upon outside the framework.
 *
 * It's bound into the service container as a singleton and is not persisted.
 *
 * @example app(Session::class)->addWarning('warning');
 */
class Session
{
    protected array $session = [];

    protected array $warnings = [];

    public function put(string $key, mixed $value): void
    {
        $this->session[$key] = $value;
    }

    public function get(string $key): mixed
    {
        return $this->session[$key] ?? null;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->session);
    }

    public function forget(string $key): void
    {
        unset($this->session[$key]);
    }

    public function addWarning(string $warning): void
    {
        $this->warnings[] = $warning;
    }

    public function getWarnings(): array
    {
        return $this->warnings;
    }

    public function hasWarnings(): bool
    {
        return ! empty($this->warnings);
    }
}
