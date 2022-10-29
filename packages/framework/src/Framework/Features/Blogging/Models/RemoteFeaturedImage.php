<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Blogging\Models;

use InvalidArgumentException;

class RemoteFeaturedImage extends FeaturedImage
{
    protected readonly string $source;

    protected function setSource(string $source): void
    {
        if (! str_starts_with($source, 'http')) {
            // Throwing an exception here ensures we have a super predictable state.
            throw new InvalidArgumentException('RemoteFeaturedImage source must start with http/https');
        }

        $this->source = $source;
    }

    public function getSource(): string
    {
        // TODO: Implement getSource() method.
    }

    public function getContentLength(): int
    {
        // TODO: Implement getContentLength() method.
    }
}
