<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Blogging\Models;

class LocalFeaturedImage extends FeaturedImage
{
    protected readonly string $source;

    public function __construct(string $source, ?string $altText, ?string $titleText, ?string $authorName, ?string $authorUrl, ?string $copyrightText, ?string $licenseName, ?string $licenseUrl)
    {
        parent::__construct($altText, $titleText, $authorName, $authorUrl, $copyrightText, $licenseName, $licenseUrl);
        $this->source = $source;
    }
}
