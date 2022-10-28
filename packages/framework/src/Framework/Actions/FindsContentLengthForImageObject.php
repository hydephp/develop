<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Framework\Features\Blogging\Models\FeaturedImage;
use Hyde\Hyde;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use function array_flip;
use function array_key_exists;
use function config;
use function file_exists;
use function filesize;
use function key;

/**
 * @see \Hyde\Framework\Testing\Feature\FindsContentLengthForImageObjectTest
 */
class FindsContentLengthForImageObject
{
    protected FeaturedImage $image;
    protected bool $shouldThrow;

    public function __construct(FeaturedImage $image)
    {
        $this->image = $image;

        $this->shouldThrow = config('hyde.throw_on_missing_image', false);
    }

    public function execute(): int
    {
        return $this->isImageStoredRemotely()
            ? $this->fetchRemoteImageInformation()
            : $this->fetchLocalImageInformation();
    }

    protected function isImageStoredRemotely(): bool
    {
        return str_starts_with($this->image->getSource(), 'http');
    }

    protected function fetchRemoteImageInformation(): int
    {
        $headers = Http::withHeaders([
            'User-Agent' => config('hyde.http_user_agent', 'RSS Request Client'),
        ])->head($this->image->getSource())->headers();

        if (array_key_exists('Content-Length', $headers)) {
            return (int) key(array_flip($headers['Content-Length']));
        }

        return $this->handleFailure('remote');
    }

    protected function fetchLocalImageInformation(): int
    {
        return file_exists($this->getLocalPath())
            ? filesize($this->getLocalPath())
            : $this->handleFailure('local');
    }

    protected function handleFailure(string $location): int
    {
        if ($this->shouldThrow) {
            throw new RuntimeException("Could not find Content-Length header for $location image {$this->image->getSource()}");
        }

        return 0;
    }

    protected function getLocalPath(): string
    {
        return Hyde::path('_media/' . $this->image->getSource());
    }
}
