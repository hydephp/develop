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
}
