<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Blogging\Models;

use Hyde\Hyde;
use Hyde\Markdown\Contracts\FrontMatter\SubSchemas\FeaturedImageSchema;
use Illuminate\Support\Str;
use BadMethodCallException;
use Stringable;

/**
 * Object representation of a blog post's featured image.
 *
 * While the object can of course be used for any other page type,
 * it is named "FeaturedImage" as it's only usage within Hyde
 * is for the featured image of a Markdown blog post.
 *
 * @see \Hyde\Framework\Factories\FeaturedImageFactory
 */
abstract class FeaturedImage implements Stringable, FeaturedImageSchema
{
    protected final const TYPE_LOCAL = 'local';
    protected final const TYPE_REMOTE = 'remote';

    /** @var self::TYPE_* */
    protected readonly string $type;
    protected readonly string $source;

    public function __construct(
        string $source = null,
        protected readonly ?string $altText = null,
        protected readonly ?string $titleText = null,
        protected readonly ?string $authorName = null,
        protected readonly ?string $authorUrl = null,
        protected readonly ?string $copyrightText = null,
        protected readonly ?string $licenseName = null,
        protected readonly ?string $licenseUrl = null
    ) {
        $this->type = str_starts_with($source, 'http') ? self::TYPE_REMOTE : self::TYPE_LOCAL;
        $this->source = $this->setSource($source);
    }

    public function __toString(): string
    {
        return $this->getSource();
    }

    /**
     * Get the source of the image, must be usable within the src attribute of an image tag,
     * and is thus not necessarily the path to the source image on disk.
     *
     * @return string The image's url or path
     */
    public function getSource(): string
    {
        if ($this->type === self::TYPE_LOCAL) {
            // Return value is always resolvable from a compiled page in the _site directory.
            return Hyde::mediaLink($this->source);
        }

        return $this->source;
    }

    /** Called from constructor to allow child classes to validate and transform the value as needed before assignment. */
    protected function setSource(string $source): string
    {
        return $source;
    }

    abstract public function getContentLength(): int;

    /** @return self::TYPE_* */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Used in resources/views/components/post/image.blade.php to add meta tags with itemprop attributes.
     *
     * @return array{text?: string|null, name?: string|null, url: string, contentUrl: string}
     */
    public function getMetadataArray(): array
    {
        $metadata = [];

        if ($this->hasAltText()) {
            $metadata['text'] = $this->getAltText();
        }

        if ($this->hasTitleText()) {
            $metadata['name'] = $this->getTitleText();
        }

        $metadata['url'] = $this->getSource();
        $metadata['contentUrl'] = $this->getSource();

        return $metadata;
    }

    public function __call(string $name, array $arguments)
    {
        if (Str::startsWith($name, 'get')) {
            $property = Str::camel(Str::after($name, 'get'));

            if (property_exists($this, $property)) {
                return $this->$property ?? null;
            }
        }

        if (Str::startsWith($name, 'has')) {
            $property = Str::camel(Str::after($name, 'has'));

            if (property_exists($this, $property)) {
                return $this->$property !== null;
            }
        }

        throw new BadMethodCallException(sprintf("Method '$name' does not exist on %s", static::class));
    }
}
