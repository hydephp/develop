<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Blogging\Models;

use Hyde\Framework\Exceptions\FileNotFoundException;
use Hyde\Hyde;

/**
 * A featured image object, for a file stored locally.
 *
 * @deprecated Will be merged into FeaturedImage.php
 *
 * The internal data structure forces the image source to reference a file in the _media directory,
 * and thus that is what is required for the input. However, when outputting data, the data will
 * be used for the _site/media directory, so it will provide data relative to the site root.
 *
 * The source information is stored in $this->source, which is a file in the _media directory.
 */
class LocalFeaturedImage extends FeaturedImage
{
    public function getContentLength(): int
    {
        return filesize($this->validatedStoragePath());
    }

    protected function validatedStoragePath(): string
    {
        $storagePath = Hyde::mediaPath($this->source);

        if (! file_exists($storagePath)) {
            throw new FileNotFoundException(sprintf('Image at %s does not exist', Hyde::pathToRelative($storagePath)));
        }

        return $storagePath;
    }
}
