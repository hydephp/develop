<?php

declare(strict_types=1);

namespace Hyde\Blogging\Models;

use Exception;
use Hyde\Framework\Actions\Constructors\FindsContentLengthForImageObject;
use Hyde\Hyde;
use Hyde\Support\Contracts\FrontMatter\Support\FeaturedImageSchema;
use Stringable;

/**
 * Holds the information for an image.
 *
 * $schema = [
 *    'path'         => '?string',
 *    'url'          => '?string',
 *    'description'  => '?string',
 *    'title'        => '?string',
 *    'copyright'    => '?string',
 *    'license'      => '?string',
 *    'licenseUrl'   => '?string',
 *    'author'       => '?string',
 *    'credit'       => '?string'
 * ];
 *
 * @see \Hyde\Framework\Testing\Feature\ImageModelTest
 * @phpstan-consistent-constructor
 */
class FeaturedImage implements FeaturedImageSchema, Stringable
{
    /**
     * The image's path (if it is stored locally (in the _media directory)).
     * Example: image.jpg.
     *
     * @var string|null
     */
    public ?string $path;

    /**
     * The image's URL (if stored externally).
     * Example: https://example.com/media/image.jpg.
     *
     * Will override the path property if both are set.
     *
     * @var string|null
     */
    public ?string $url;

    /**
     * The image's description. (Used for alt text for screen readers.)
     * You should always set this to provide accessibility.
     * Example: "This is an image of a cat sitting in a basket.".
     *
     * @var string|null
     */
    public ?string $description;

    /**
     * The image's title. (Shows a tooltip on hover.)
     * Example: "My Cat Archer".
     *
     * @var string|null
     */
    public ?string $title;

    /**
     * The image's copyright.
     * Example: "Copyright (c) 2020 John Doe".
     *
     * @var string|null
     */
    public ?string $copyright;

    /**
     * The image's license name.
     * Example: "CC BY-NC-SA 4.0".
     *
     * @var string|null
     */
    public ?string $license;

    /**
     * The image's license URL.
     * Example: "https://creativecommons.org/licenses/by-nc-sa/4.0/".
     *
     * @var string|null
     */
    public ?string $licenseUrl;

    /**
     * The image's author.
     * Example: "John Doe".
     *
     * @var string|null
     */
    public ?string $author;

    /**
     * The image's source (for attribution).
     * Example: "https://unsplash.com/photos/example".
     *
     * @var string|null
     */
    public ?string $credit = null;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }

        if (isset($this->path)) {
            $this->path = basename($this->path);
        }
    }

    /** @inheritDoc */
    public function __toString()
    {
        return $this->getLink();
    }

    /** Dynamically create an image based on string or front matter array */
    public static function make(string|array $data): static
    {
        if (is_string($data)) {
            return static::fromSource($data);
        }

        return new static($data);
    }

    public static function fromSource(string $image): static
    {
        return str_starts_with($image, 'http')
            ? new static(['url' => $image])
            : new static(['path' => $image]);
    }

    public function getSource(): string
    {
        return $this->url ?? $this->getPath() ?? throw new Exception('Attempting to get source from Image that has no source.');
    }

    public function getLink(): string
    {
        return Hyde::image($this->getSource());
    }

    public function getContentLength(): int
    {
        return (new FindsContentLengthForImageObject($this))->execute();
    }

    public function getImageAuthorAttributionString(): string|null
    {
        if (isset($this->author)) {
            if (isset($this->credit)) {
                return '<span itemprop="creator" itemscope="" itemtype="http://schema.org/Person"><a href="'.e($this->credit).'" rel="author noopener nofollow" itemprop="url"><span itemprop="name">'.e($this->author).'</span></a></span>';
            } else {
                return '<span itemprop="creator" itemscope="" itemtype="http://schema.org/Person"><span itemprop="name">'.e($this->author).'</span></span>';
            }
        }

        return null;
    }

    public function getCopyrightString(): string|null
    {
        if (isset($this->copyright)) {
            return '<span itemprop="copyrightNotice">'.e($this->copyright).'</span>';
        }

        return null;
    }

    public function getLicenseString(): string|null
    {
        if (isset($this->license) && isset($this->licenseUrl)) {
            return '<a href="'.e($this->licenseUrl).'" rel="license nofollow noopener" itemprop="license">'.e($this->license).'</a>';
        }

        if (isset($this->license)) {
            return '<span itemprop="license">'.e($this->license).'</span>';
        }

        return null;
    }

    public function getFluentAttribution(): string
    {
        $attribution = [];

        $getImageAuthorAttributionString = $this->getImageAuthorAttributionString();
        if ($getImageAuthorAttributionString !== null) {
            $attribution[] = 'Image by '.$getImageAuthorAttributionString;
        }

        $getCopyrightString = $this->getCopyrightString();
        if ($getCopyrightString !== null) {
            $attribution[] = $getCopyrightString;
        }

        $getLicenseString = $this->getLicenseString();
        if ($getLicenseString !== null) {
            $attribution[] = 'License '.$getLicenseString;
        }

        return implode('. ', $attribution);
    }

    /**
     * Used in resources\views\components\post\image.blade.php to add meta tags with itemprop attributes.
     *
     * @return array
     */
    public function getMetadataArray(): array
    {
        $metadata = [];

        if (isset($this->description)) {
            $metadata['text'] = $this->description;
        }

        if (isset($this->title)) {
            $metadata['name'] = $this->title;
        }

        $metadata['url'] = $this->getLink();
        $metadata['contentUrl'] = $this->getLink();

        return $metadata;
    }

    protected function getPath(): ?string
    {
        if (isset($this->path)) {
            return basename($this->path);
        }

        return null;
    }
}
