<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Blogging\Models;

use Hyde\Framework\Exceptions\FileNotFoundException;
use Hyde\Hyde;
use Illuminate\Support\Str;

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
        // We could also validate the file exists here if we want.
        // We might also want to just send a warning. But for now,
        // we'll just trim any leading media path prefixes.

        return Str::after($source, Hyde::getMediaDirectory().'/');
    }

    public function getSource(): string
    {
        // Return value is always resolvable from a compiled page in the _site directory.
        return Hyde::mediaLink($this->source);
    }

    public function getContentLength(): int
    {
        return filesize($this->validatedStoragePath());
    }

    protected function storagePath(): string
    {
        return Hyde::mediaPath($this->source);
    }

    protected function validatedStoragePath(): string
    {
        if (! file_exists($this->storagePath())) {
            throw new FileNotFoundException(sprintf('Image at %s does not exist', Hyde::pathToRelative($this->storagePath())));
        }

        return $this->storagePath();
    }
}
