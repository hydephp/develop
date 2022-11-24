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

    /**
     * @return string Relative path
     */
    public function getOutputPath(): string;

    public function force(bool $force = true): void;

    public function pathConflicts(): bool;
}
