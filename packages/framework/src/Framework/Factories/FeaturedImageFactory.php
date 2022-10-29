<?php

declare(strict_types=1);

namespace Hyde\Framework\Factories;

use Hyde\Framework\Concerns\InteractsWithFrontMatter;
use Hyde\Framework\Features\Blogging\Models\FeaturedImage;
use Hyde\Framework\Features\Blogging\Models\LocalFeaturedImage;
use Hyde\Framework\Features\Blogging\Models\RemoteFeaturedImage;
use Hyde\Hyde;
use Hyde\Markdown\Contracts\FrontMatter\SubSchemas\FeaturedImageSchema;
use Hyde\Markdown\Models\FrontMatter;
use function is_string;
use RuntimeException;
use function str_starts_with;

class FeaturedImageFactory extends Concerns\PageDataFactory implements FeaturedImageSchema
{
    use InteractsWithFrontMatter;

    public const SCHEMA = FeaturedImageSchema::FEATURED_IMAGE_SCHEMA;

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

        if (str_starts_with($data['source'], '_media')) {
            return new LocalFeaturedImage(...$data);
        }

        return new RemoteFeaturedImage(...$data);
    }

    protected function makeSource(): string
    {
        if (is_string($this->matter('image'))) {
            return $this->matter('image');
        }

        if ($this->matter('image.url') !== null) {
            return $this->matter('image.url');
        }

        if ($this->matter('image.path') !== null) {
            return $this->normalizeLocalImagePath($this->matter('image.path'));
        }

        // Todo, we might want to add a note about which file caused the error.
        // We could also check for these before calling the factory, and just ignore the image if it's not valid.
        throw new RuntimeException('No featured image source was found');
    }

    protected function makeAltText(): ?string
    {
        return $this->matter('image.description');
    }

    protected function makeTitleText(): ?string
    {
        return $this->matter('image.title');
    }

    protected function makeAuthorName(): ?string
    {
        return $this->matter('image.author');
    }

    protected function makeAuthorUrl(): ?string
    {
        return $this->matter('image.attributionUrl');
    }

    protected function makeCopyrightText(): ?string
    {
        return $this->matter('image.copyright');
    }

    protected function makeLicenseName(): ?string
    {
        return $this->matter('image.license');
    }

    protected function makeLicenseUrl(): ?string
    {
        return $this->matter('image.licenseUrl');
    }

    protected static function normalizeLocalImagePath(string $path): string
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
