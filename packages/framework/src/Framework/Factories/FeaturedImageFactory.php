<?php

declare(strict_types=1);

namespace Hyde\Framework\Factories;

use Hyde\Hyde;
use RuntimeException;
use Illuminate\Support\Str;
use Hyde\Markdown\Models\FrontMatter;
use Hyde\Framework\Features\Blogging\Models\FeaturedImage;
use Hyde\Markdown\Contracts\FrontMatter\SubSchemas\FeaturedImageSchema;

use function str_starts_with;
use function is_string;
use function unslash;

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
        private readonly ?string $filePath = null,
    ) {
        $this->source = $this->makeSource();
        $this->altText = $this->getStringMatter('image.altText');
        $this->titleText = $this->getStringMatter('image.titleText');
        $this->authorName = $this->getStringMatter('image.authorName');
        $this->authorUrl = $this->getStringMatter('image.authorUrl');
        $this->copyrightText = $this->getStringMatter('image.copyright');
        $this->licenseName = $this->getStringMatter('image.licenseName');
        $this->licenseUrl = $this->getStringMatter('image.licenseUrl');
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

    public static function make(FrontMatter $matter, ?string $filePath = null): FeaturedImage
    {
        return new FeaturedImage(...(new static($matter, $filePath))->toArray());
    }

    protected function makeSource(): string
    {
        $value = $this->getStringMatter('image') ?? $this->getStringMatter('image.source');

        if (empty($value)) {
            throw new RuntimeException(sprintf('No featured image source was found in "%s"', $this->filePath ?? 'unknown file'));
        }

        if (FeaturedImage::isRemote($value)) {
            return $value;
        }

        return self::normalizeLocalImagePath($value);
    }

    protected static function normalizeLocalImagePath(string $path): string
    {
        $path = Hyde::pathToRelative($path);

        $path = Str::after($path, Hyde::getMediaDirectory());
        $path = Str::after($path, Hyde::getMediaOutputDirectory());

        return str_starts_with($path, '//') ? $path : unslash($path);
    }

    protected function getStringMatter(string $key): ?string
    {
        return is_string($this->matter->get($key)) ? $this->matter->get($key) : null;
    }
}
