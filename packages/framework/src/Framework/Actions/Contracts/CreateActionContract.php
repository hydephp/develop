<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions\Contracts;

/**
 * @see \Hyde\Framework\Actions\Concerns\CreateAction
 */
interface CreateActionContract
{
    public function create(): void;
}
