<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Blogging\Models;

use Stringable;

abstract class FeaturedImage implements Stringable
{
    protected readonly ?string $altText = null;
    protected readonly ?string $titleText = null;

    protected readonly ?string $authorName = null;
    protected readonly ?string $authorUrl = null;

    protected readonly ?string $copyrightText = null;
    protected readonly ?string $licenseName = null;
    protected readonly ?string $licenseUrl = null;

    /**
     * Get the source of the image, must be usable within the src attribute of an image tag.
     * @return string The image's url or path
     */
    abstract public function getSource(): string;
}
