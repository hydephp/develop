<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Blogging\Models;

use function array_flip;
use function array_key_exists;
use function config;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use function key;

class RemoteFeaturedImage extends FeaturedImage
{
    protected readonly string $source;

    protected function setSource(string $source): void
    {
        if (! str_starts_with($source, 'http')) {
            // Throwing an exception here ensures we have a super predictable state.
            throw new InvalidArgumentException('RemoteFeaturedImage source must be a valid URL');
        }

        $this->source = $source;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getContentLength(): int
    {
        $headers = Http::withHeaders([
            'User-Agent' => config('hyde.http_user_agent', 'RSS Request Client'),
        ])->head($this->getSource())->headers();

        if (array_key_exists('Content-Length', $headers)) {
            return (int) key(array_flip($headers['Content-Length']));
        }

        // Here we could throw an exception if we want to be strict about this, or add a warning.

        return 0;
    }
}
