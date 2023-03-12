<?php

declare(strict_types=1);

namespace Hyde\Support\Filesystem;

use Hyde\Pages\Concerns\HydePage;
use Illuminate\Support\Str;

/**
 * File abstraction for a project source file.
 *
 * @see \Hyde\Foundation\Kernel\FileCollection
 */
class SourceFile extends ProjectFile
{
    /**
     * The associated page class string.
     *
     * @var class-string<\Hyde\Pages\Concerns\HydePage>
     */
    public readonly string $pageClass;

    /** @param  class-string<\Hyde\Pages\Concerns\HydePage>  $pageClass */
    public static function make(string $path, string $pageClass = HydePage::class): static
    {
        return new static($path, $pageClass);
    }

    /** @param  class-string<\Hyde\Pages\Concerns\HydePage>  $pageClass */
    public function __construct(string $path, string $pageClass = HydePage::class)
    {
        parent::__construct($path);
        $this->pageClass = $pageClass;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'pageClass' => $this->pageClass,
        ]);
    }
}
