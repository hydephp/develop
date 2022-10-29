<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Blogging\Models;

use function func_get_args;

class LocalFeaturedImage extends FeaturedImage
{
    protected readonly string $source;

    public function __construct(string $source, ?string $altText, ?string $titleText, ?string $authorName, ?string $authorUrl, ?string $copyrightText, ?string $licenseName, ?string $licenseUrl)
    {
        parent::__construct(...func_get_args());
        $this->source = $source;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getContentLength(): int
    {
        return filesize($this->source);
    }
}
