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

    /**
     * @return bool Does a file at the output path already exist?
     */
    public function fileExists(): bool;

    /**
     * @return bool Will the action cause a file conflict exception?
     */
    public function fileConflicts(): bool;
}
