<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Framework\Features\Blogging\Models\FeaturedImage;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use function array_flip;
use function array_key_exists;
use function config;
use function key;

/**
 * @see \Hyde\Framework\Testing\Feature\FindsContentLengthForImageObjectTest
 */
class FindsContentLengthForRemoteImageObject
{
    protected FeaturedImage $image;

    public function __construct(FeaturedImage $image)
    {
        $this->image = $image;
    }

    public function execute(): int
    {
        $headers = Http::withHeaders([
            'User-Agent' => config('hyde.http_user_agent', 'RSS Request Client'),
        ])->head($this->image->getSource())->headers();

        if (array_key_exists('Content-Length', $headers)) {
            return (int) key(array_flip($headers['Content-Length']));
        }

        throw new RuntimeException('Could not find Content-Length header for ' . $this->image->getSource());
    }
}
