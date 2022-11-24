<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions\Concerns;

use Hyde\Framework\Actions\Contracts\CreateActionContract;

/**
 * @see \Hyde\Framework\Testing\Feature\CreateActionTest
 */
abstract class CreateAction implements CreateActionContract
{
    /** @inheritDoc */
    public function create(): void
    {
        // TODO: Implement create() method.
    }

    /** @inheritDoc */
    public function getOutputPath(): string
    {
        // TODO: Implement getOutputPath() method.
    }

    public function force(bool $force = true): void
    {
        // TODO: Implement force() method.
    }

    public function pathConflicts(): bool
    {
        // TODO: Implement pathConflicts() method.
    }
}
