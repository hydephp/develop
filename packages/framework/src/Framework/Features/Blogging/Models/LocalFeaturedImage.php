<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Blogging\Models;


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
    //
}
