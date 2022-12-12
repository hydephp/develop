<?php

declare(strict_types=1);

namespace Hyde\Support\Filesystem;

use function basename;
use function strlen;
use function substr;

/**
 * File abstraction for a project source file.
 *
 * @see \Hyde\Foundation\FileCollection
 */
class SourceFile extends ProjectFile
{
    /**
     * The associated page class string.
     *
     * @var class-string<\Hyde\Pages\Concerns\HydePage>
     */
    public readonly string $model;

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'model' => $this->model,
        ]);
    }

    public function withoutDirectoryPrefix(): string
    {
        // Works like basename, but keeps subdirectory names.
        return substr($this->__toString(), strlen($this->model::$sourceDirectory) + 1);
    }
}
