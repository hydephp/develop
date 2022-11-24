<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions\Contracts;

/**
 * @see \Hyde\Framework\Actions\Concerns\CreateAction
 */
interface CreateActionContract
{
    /**
     * @throws \Hyde\Framework\Exceptions\FileConflictException
     */
    public function create(): void;
}
