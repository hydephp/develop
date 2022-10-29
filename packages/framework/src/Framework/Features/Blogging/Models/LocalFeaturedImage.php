<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Blogging\Models;

use Hyde\Hyde;
use InvalidArgumentException;
use function str_starts_with;

class LocalFeaturedImage extends FeaturedImage
{
    protected readonly string $source;

    protected function setSource(string $source): void
    {
        if (! str_starts_with($source, '_media/')) {
            // Throwing an exception here ensures we have a super predictable state.
            throw new InvalidArgumentException('LocalFeaturedImage source must start with _media/');
        }

        // We could also validate the file exists here if we want. We might also want to just send a warning.

        $this->source = $source;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getContentLength(): int
    {
        return filesize($this->storagePath());
    }

    protected function storagePath(): string
    {
        return Hyde::path($this->source);
    }

    protected function storageValidatedPath(): string
    {
        $path = $this->storagePath();

        if (! file_exists($path)) {
            throw new InvalidArgumentException("Image at $this->source does not exist");
        }

        return $path;
    }
}
