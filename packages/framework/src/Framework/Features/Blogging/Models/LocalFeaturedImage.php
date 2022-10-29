<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Blogging\Models;

use function func_get_args;

class LocalFeaturedImage extends FeaturedImage
{
    protected readonly string $source;

    protected function setSource(string $source): void
    {
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
