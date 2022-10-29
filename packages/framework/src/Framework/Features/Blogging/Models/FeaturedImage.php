<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Blogging\Models;

use function array_flip;
use function array_key_exists;
use BadMethodCallException;
use function config;
use function e;
use function file_exists;
use Hyde\Hyde;
use Hyde\Markdown\Contracts\FrontMatter\SubSchemas\FeaturedImageSchema;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use function implode;
use function key;
use Stringable;

/**
 * Holds the information for an image, and contains helper methods for generating fluent HTML around it.
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
 *    'attributionUrl' => '?string'
 * ];
 *
 * @see \Hyde\Framework\Testing\Feature\ImageModelTest
 * @phpstan-consistent-constructor
 */
class FeaturedImage implements FeaturedImageSchema, Stringable
{
    /**
     * The image's path if it's stored locally.
     * The image must be stored in the _media directory.
     *
     * It no longer matters if you add the _media/ prefix or not,
     * as it will be normalized to have the prefix stripped.
     *
     * @example image.jpg.
     * @example _media/image.jpg. (will be normalized to image.jpg)
     */
    public ?string $path;

    /**
     * The image's URL if it's stored remotely.
     * Will override the path property if both are set.
     *
     * @example https://example.com/media/image.jpg
     */
    public ?string $url;

    /**
     * The image's description. (Used for alt text for screen readers.)
     * You should always set this to provide accessibility.
     *
     * @example "This is an image of a cat sitting in a basket.".
     */
    public ?string $description;

    /**
     * The image's title. (Shows a tooltip on hover.).
     *
     * @example "My Cat Archer".
     */
    public ?string $title;

    /**
     * The image's copyright information.
     *
     * @example "Copyright (c) 2020 John Doe".
     */
    public ?string $copyright;

    /**
     * The image's license name.
     *
     * @example "CC BY-NC-SA 4.0".
     */
    public ?string $license;

    /**
     * The image's license URL.
     *
     * @example "https://creativecommons.org/licenses/by-nc-sa/4.0/".
     */
    public ?string $licenseUrl;

    /**
     * The image's author/photographer.
     *
     * @example "John Doe".
     */
    public ?string $author;

    /**
     * Link to the image author/source (for attribution/credit).
     * When added, the rendered $author's name will link to this URL.
     *
     * @note This was previously called "credit" but was renamed to "attributionUrl" for clarity.
     *
     * @example "https://unsplash.com/photos/example".
     */
    public ?string $attributionUrl = null;

    /**
     * @var string The path to the image in the _media directory, relative to the root of the site.
     *
     * @example "_media/image.jpg".
     */
    protected string $sourcePath;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }

        if (isset($this->path)) {
            $this->sourcePath = static::normalizeSourcePath($this->path);
            $this->path = Str::after($this->sourcePath, '_media/');
        }
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

    /** @inheritDoc */
    public function __toString()
    {
        return $this->getLink();
    }

    public function getSource(): string
    {
        return $this->url ?? $this->getPath() ?? throw new BadMethodCallException('Attempting to get source from Image that has no source.');
    }

    public function getLink(): string
    {
        return Hyde::image($this->getSource());
    }

    public function getPath(): ?string
    {
        return $this->path ?? null;
    }

    public function getSourcePath(): ?string
    {
        return $this->sourcePath ?? null;
    }

    public function getContentLength(): int
    {
        return (str_starts_with($this->getSource(), 'http')
            ? $this->getContentLengthFromRemote()
            : $this->getLocalContentLength()) ?? 0;
    }

    public function getFluentAttribution(): HtmlString
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

        return new HtmlString(implode('. ', $attribution).((count($attribution) > 0) ? '.' : ''));
    }

    /**
     * Used in resources/views/components/post/image.blade.php to add meta tags with itemprop attributes.
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

    /** @internal */
    public function getImageAuthorAttributionString(): string|null
    {
        if (isset($this->author)) {
            return '<span itemprop="creator" itemscope="" itemtype="http://schema.org/Person">'.$this->getAuthorElement().'</span>';
        }

        return null;
    }

    /** @internal */
    public function getCopyrightString(): string|null
    {
        if (isset($this->copyright)) {
            return '<span itemprop="copyrightNotice">'.e($this->copyright).'</span>';
        }

        return null;
    }

    /** @internal */
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

    protected function getAuthorLink(): string
    {
        return '<a href="'.e($this->attributionUrl).'" rel="author noopener nofollow" itemprop="url">'.$this->getAuthorSpan().'</a>';
    }

    protected function getAuthorSpan(): string
    {
        return '<span itemprop="name">'.e($this->author).'</span>';
    }

    protected function getAuthorElement(): string
    {
        return isset($this->attributionUrl)
            ? $this->getAuthorLink()
            : $this->getAuthorSpan();
    }

    protected function getContentLengthFromRemote(): ?int
    {
        $headers = Http::withHeaders([
            'User-Agent' => config('hyde.http_user_agent', 'RSS Request Client'),
        ])->head($this->getSource())->headers();

        if (array_key_exists('Content-Length', $headers)) {
            return (int) key(array_flip($headers['Content-Length']));
        }

        return null;
    }

    protected function getLocalContentLength(): ?int
    {
        if (isset($this->sourcePath) && file_exists(Hyde::path($this->getSourcePath()))) {
            return filesize(Hyde::path($this->getSourcePath()));
        }

        return null;
    }

    protected static function normalizeSourcePath(string $path): string
    {
        $path = Hyde::pathToRelative($path);

        if (str_starts_with($path, '_media/')) {
            return $path;
        }

        if (str_starts_with($path, 'media/')) {
            return '_'.$path;
        }

        return '_media/'.$path;
    }
}
