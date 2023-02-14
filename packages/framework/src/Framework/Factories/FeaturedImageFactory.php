<?php

declare(strict_types=1);

namespace Hyde\Framework\Factories;

use Hyde\Framework\Features\Blogging\Models\FeaturedImage;
use Hyde\Framework\Features\Blogging\Models\LocalFeaturedImage;
use Hyde\Framework\Features\Blogging\Models\RemoteFeaturedImage;
use Hyde\Hyde;
use Hyde\Markdown\Contracts\FrontMatter\SubSchemas\FeaturedImageSchema;
use Hyde\Markdown\Models\FrontMatter;
use Illuminate\Support\Str;
use function is_string;
use RuntimeException;
use function str_starts_with;

class FeaturedImageFactory extends Concerns\PageDataFactory implements FeaturedImageSchema
{
    final public const SCHEMA = FeaturedImageSchema::FEATURED_IMAGE_SCHEMA;

    protected readonly string $source;
    protected readonly ?string $altText;
    protected readonly ?string $titleText;
    protected readonly ?string $authorName;
    protected readonly ?string $authorUrl;
    protected readonly ?string $copyrightText;
    protected readonly ?string $licenseName;
    protected readonly ?string $licenseUrl;

    public function __construct(
        private readonly FrontMatter $matter,
    ) {
        $this->source = $this->makeSource();
        $this->altText = $this->makeAltText();
        $this->titleText = $this->makeTitleText();
        $this->authorName = $this->makeAuthorName();
        $this->authorUrl = $this->makeAuthorUrl();
        $this->copyrightText = $this->makeCopyrightText();
        $this->licenseName = $this->makeLicenseName();
        $this->licenseUrl = $this->makeLicenseUrl();
    }

    /**
     * @return array{source: string, altText: string|null, titleText: string|null, authorName: string|null, authorUrl: string|null, copyrightText: string|null, licenseName: string|null, licenseUrl: string|null}
     */
    public function toArray(): array
    {
        return [
            'source' => $this->source,
            'altText' => $this->altText,
            'titleText' => $this->titleText,
            'authorName' => $this->authorName,
            'authorUrl' => $this->authorUrl,
            'copyrightText' => $this->copyrightText,
            'licenseName' => $this->licenseName,
            'licenseUrl' => $this->licenseUrl,
        ];
    }

    public static function make(FrontMatter $matter): FeaturedImage
    {
        $data = (new static($matter))->toArray();

        if (self::isRemote($matter)) {
            return new RemoteFeaturedImage(...$data);
        }

        return new LocalFeaturedImage(...$data);
    }

    protected function makeSource(): string
    {
        if (is_string($this->getStringMatter('image'))) {
            if (str_starts_with($this->getStringMatter('image'), 'http')) {
                return $this->getStringMatter('image');
            }

            return self::normalizeLocalImagePath($this->getStringMatter('image'));
        }

        if ($this->getStringMatter('image.url') !== null) {
            return $this->getStringMatter('image.url');
        }

        if ($this->getStringMatter('image.path') !== null) {
            return $this->normalizeLocalImagePath($this->getStringMatter('image.path'));
        }

        // Todo, we might want to add a note about which file caused the error.
        // We could also check for these before calling the factory, and just ignore the image if it's not valid.
        throw new RuntimeException('No featured image source was found');
    }

    protected function makeAltText(): ?string
    {
        return $this->getStringMatter('image.description');
    }

    protected function makeTitleText(): ?string
    {
        return $this->getStringMatter('image.title');
    }

    protected function makeAuthorName(): ?string
    {
        return $this->getStringMatter('image.author');
    }

    protected function makeAuthorUrl(): ?string
    {
        return $this->getStringMatter('image.attributionUrl');
    }

    protected function makeCopyrightText(): ?string
    {
        return $this->getStringMatter('image.copyright');
    }

    protected function makeLicenseName(): ?string
    {
        return $this->getStringMatter('image.license');
    }

    protected function makeLicenseUrl(): ?string
    {
        return $this->getStringMatter('image.licenseUrl');
    }

    protected static function normalizeLocalImagePath(string $path): string
    {
        $path = Hyde::pathToRelative($path);

        $path = Str::after($path, Hyde::getMediaDirectory());
        $path = Str::after($path, Hyde::getMediaOutputDirectory());

        return unslash($path);
    }

    protected static function isRemote(FrontMatter $matter): bool
    {
        if (is_string($matter->get('image')) && str_starts_with($matter->get('image'), 'http')) {
            return true;
        }

        return $matter->get('image.url') !== null;
    }

    protected function getStringMatter(string $key): ?string
    {
        return is_string($this->matter->get($key)) ? $this->matter->get($key) : null;
    }
}
