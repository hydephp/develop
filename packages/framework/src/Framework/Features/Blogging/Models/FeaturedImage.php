<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Blogging\Models;

use BadMethodCallException;
use Hyde\Framework\Actions\Constructors\FindsContentLengthForImageObject;
use Hyde\Hyde;
use Hyde\Markdown\Contracts\FrontMatter\SubSchemas\FeaturedImageSchema;
use Stringable;
use function e;

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
        return $this->url ?? $this->getPath() ?? throw new BadMethodCallException('Attempting to get source from Image that has no source.');
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
        return isset($this->author) ? $this->makeAuthorString() : null;

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

        if ($this->getImageAuthorAttributionString() !== null) {
            $attribution[] = 'Image by '.$this->getImageAuthorAttributionString();
        }

        if ($this->getCopyrightString() !== null) {
            $attribution[] = $this->getCopyrightString();
        }

        if ($this->getLicenseString() !== null) {
            $attribution[] = 'License '.$this->getLicenseString();
        }

        return implode('. ', $attribution).((count($attribution) > 0) ? '.' : '');
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

    protected function getCreditedAuthorLink(): string
    {
        return '<a href="' . e($this->credit) . '" rel="author noopener nofollow" itemprop="url"><span itemprop="name">' . e($this->author) . '</span></a>';
    }

    protected function getAuthorSpan(): string
    {
        return '<span itemprop="name">' . e($this->author) . '</span>';
    }

    protected function makeAuthorString(): string
    {
        return sprintf('<span itemprop="creator" itemscope="" itemtype="http://schema.org/Person">%s</span>', isset($this->credit)
            ? $this->getCreditedAuthorLink()
            : $this->getAuthorSpan());
    }
}
