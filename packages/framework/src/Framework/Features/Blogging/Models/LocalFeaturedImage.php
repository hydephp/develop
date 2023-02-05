<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Blogging\Models;

use Hyde\Framework\Exceptions\FileNotFoundException;
use Hyde\Hyde;
use InvalidArgumentException;
use function str_starts_with;
use function substr;

/**
 * A featured image object, for a file stored locally.
 *
 * The internal data structure forces the image source to reference a file in the _media directory,
 * and thus that is what is required for the input. However, when outputting data, the data will
 * be used for the _site/media directory, so it will provide data relative to the site root.
 *
 * The source information is stored in $this->source, which is a file in the _media directory.
 */
class LocalFeaturedImage extends FeaturedImage
{
    protected function setSource(string $source): string
    {
        if (str_starts_with($source, '_media/')) {
            $source = substr($source, 7);
        }

        if (str_starts_with($source, '_site/media/')) {
            $source = substr($source, 12);
        }

        // We could also validate the file exists here if we want. We might also want to just send a warning.

        return $source;
    }

    public function getSource(): string
    {
        // Return value must be relative to the site's root.
        return Hyde::relativeLink(substr($this->source, 1));
    }

    public function getContentLength(): int
    {
        return filesize($this->storageValidatedPath());
    }

    protected function storagePath(): string
    {
        return Hyde::path($this->source);
    }

    protected function storageValidatedPath(): string
    {
        if (! file_exists($this->storagePath())) {
            throw new FileNotFoundException("Image at $this->source does not exist");
        }

        return $this->storagePath();
    }
}
