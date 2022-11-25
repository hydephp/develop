<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions\Contracts;

/**
 * @see \Hyde\Framework\Actions\Concerns\CreateAction
 *
 * @internal This class is experimental and is not meant to be used outside the Hyde framework.
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
     * @param  bool  $force  Should existing files at the output path be overwritten?
     * @return $this
     */
    public function force(bool $force = true): static;

    /**
     * @param  string  $outputPath  Relative path.
     * @return $this
     */
    public function setOutputPath(string $outputPath): static;

    /**
     * @return string Relative path.
     */
    public function getOutputPath(): string;

    /**
     * @return string Absolute path.
     */
    public function getAbsoluteOutputPath(): string;

    /**
     * @return bool Does a file at the output path already exist?
     */
    public function fileExists(): bool;

    /**
     * @return bool Will the action cause a file conflict exception?
     */
    public function hasFileConflict(): bool;
}
