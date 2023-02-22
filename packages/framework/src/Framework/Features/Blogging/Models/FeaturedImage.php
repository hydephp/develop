<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Blogging\Models;

use Hyde\Framework\Exceptions\FileNotFoundException;
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
 * @method getAltText(): ?string
 * @method getTitleText(): ?string
 * @method getAuthorName(): ?string
 * @method getAuthorUrl(): ?string
 * @method getCopyrightText(): ?string
 * @method getLicenseName(): ?string
 * @method getLicenseUrl(): ?string
 * @method hasAltText(): bool
 * @method hasTitleText(): bool
 * @method hasAuthorName(): bool
 * @method hasAuthorUrl(): bool
 * @method hasCopyrightText(): bool
 * @method hasLicenseName(): bool
 * @method hasLicenseUrl(): bool
 *
 * @see \Hyde\Framework\Factories\FeaturedImageFactory
 */
abstract class FeaturedImage implements Stringable, FeaturedImageSchema
{
    /** @deprecated Can be implicitly determined by source prefix */
    protected final const TYPE_LOCAL = 'local';

    /** @deprecated Can be implicitly determined by source prefix */
    protected final const TYPE_REMOTE = 'remote';

    /**
     * @deprecated Can be implicitly determined by source prefix
     *
     * @var self::TYPE_*
     */
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

    protected function setSource(string $source): string
    {
        if ($this->type === self::TYPE_LOCAL) {
            // Normalize away any leading media path prefixes.

            return Str::after($source, Hyde::getMediaDirectory().'/');
        }

        return $source;
    }

    public function getContentLength(): int
    {
        if ($this->type === self::TYPE_LOCAL) {
            $storagePath = Hyde::mediaPath($this->source);

            if (! file_exists($storagePath)) {
                throw new FileNotFoundException(sprintf('Image at %s does not exist', Hyde::pathToRelative($storagePath)));
            }

            return filesize($storagePath);
        }

        return 0;
    }

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

    public function __call(string $name, array $arguments): null|bool|string
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
