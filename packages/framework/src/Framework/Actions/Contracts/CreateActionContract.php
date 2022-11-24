<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions\Contracts;

/**
 * @see \Hyde\Framework\Actions\Concerns\CreateAction
 */
interface CreateActionContract
{
    /**
     * Create the file at the configured output path.
     *
     * @throws \Hyde\Framework\Exceptions\FileConflictException
     */
    public function create(): void;

    /**
     * @return string Relative path.
     */
    public function getOutputPath(): string;

    /**
     * @param string $outputPath Relative path.
     * @return $this
     */
    public function setOutputPath(string $outputPath): static;

    /**
     * @param bool $force Should existing files at the output path be overwritten?
     * @return $this
     */
    public function force(bool $force = true): static;

    /**
     * @return bool Does a file at the output path already exist?
     */
    public function fileExists(): bool;

    /**
     * @return bool Will the action cause a file conflict exception?
     */
    public function fileConflicts(): bool;
}
