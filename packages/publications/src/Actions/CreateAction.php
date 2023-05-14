<?php

declare(strict_types=1);

namespace Hyde\Publications\Actions;

use Hyde\Hyde;
use Illuminate\Support\Str;
use Hyde\Framework\Exceptions\FileConflictException;
use Hyde\Framework\Concerns\InteractsWithDirectories;

use function file_exists;
use function file_put_contents;

/**
 * @see \Hyde\Publications\Testing\Feature\CreateActionTest
 *
 * @internal This class is experimental and is not meant to be used outside the Hyde framework.
 */
abstract class CreateAction
{
    use InteractsWithDirectories;

    protected string $outputPath;
    protected bool $force = false;

    abstract protected function handleCreate(): void;

    /**
     * Create the file at the configured output path.
     *
     * @throws \Hyde\Framework\Exceptions\FileConflictException
     */
    public function create(): void
    {
        if ($this->hasFileConflict()) {
            throw new FileConflictException($this->outputPath);
        }

        $this->handleCreate();
    }

    /**
     * @param  bool  $force  Should existing files at the output path be overwritten?
     * @return $this
     */
    public function force(bool $force = true): static
    {
        $this->force = $force;

        return $this;
    }

    /**
     * @param  string  $outputPath  Relative path.
     * @return $this
     */
    public function setOutputPath(string $outputPath): static
    {
        $this->outputPath = $outputPath;

        return $this;
    }

    /**
     * @return string Relative path.
     */
    public function getOutputPath(): string
    {
        return $this->outputPath;
    }

    /**
     * @return string Absolute path.
     */
    public function getAbsoluteOutputPath(): string
    {
        return Hyde::path($this->getOutputPath());
    }

    /**
     * @return bool Does a file at the output path already exist?
     */
    public function fileExists(): bool
    {
        return file_exists($this->getAbsoluteOutputPath());
    }

    /**
     * @return bool Will the action cause a file conflict exception?
     */
    public function hasFileConflict(): bool
    {
        return $this->fileExists() && ! $this->force;
    }

    protected function save(string $contents): void
    {
        $this->needsParentDirectory($this->getAbsoluteOutputPath());
        file_put_contents($this->getAbsoluteOutputPath(), $contents);
    }

    protected function formatStringForStorage(string $string): string
    {
        return Str::slug($string);
    }
}
