<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Blogging\Models;

use Stringable;

abstract class FeaturedImage implements Stringable
{
    /**
     * Get the source of the image, must be usable within the src attribute of an image tag.
     * @return string The image's url or path
     */
    abstract public function getSource(): string;
}
