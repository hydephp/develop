<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions\Contracts;

/**
 * @see \Hyde\Framework\Actions\Concerns\CreateAction
 *
 * @deprecated 
 * @internal This class is experimental and is not meant to be used outside the Hyde framework.
 */
interface CreateActionContract
{
    public function create(): void;

    public function force(bool $force = true): static;

    public function setOutputPath(string $outputPath): static;

    public function getOutputPath(): string;

    public function getAbsoluteOutputPath(): string;

    public function fileExists(): bool;

    public function hasFileConflict(): bool;
}
