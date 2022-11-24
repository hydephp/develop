<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions\Concerns;

use Hyde\Framework\Actions\Contracts\CreateActionContract;

/**
 * @see \Hyde\Framework\Testing\Feature\CreateActionTest
 */
abstract class CreateAction implements CreateActionContract
{
    protected string $outputPath;
    protected bool $force = false;

    /** @inheritDoc */
    public function create(): void
    {
        // TODO: Implement create() method.
    }

    /** @inheritDoc */
    public function getOutputPath(): string
    {
        return $this->outputPath;
    }

    public function force(bool $force = true): void
    {
        $this->force = $force;
    }

    public function pathConflicts(): bool
    {
        // TODO: Implement pathConflicts() method.
    }
}
