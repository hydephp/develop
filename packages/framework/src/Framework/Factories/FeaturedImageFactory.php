<?php

declare(strict_types=1);

namespace Hyde\Framework\Factories;

use Hyde\Framework\Concerns\InteractsWithFrontMatter;
use Hyde\Markdown\Contracts\FrontMatter\SubSchemas\FeaturedImageSchema;
use Hyde\Markdown\Models\FrontMatter;

class FeaturedImageFactory extends Concerns\PageDataFactory implements FeaturedImageSchema
{
    use InteractsWithFrontMatter;

    public const SCHEMA = FeaturedImageSchema::FEATURED_IMAGE_SCHEMA;

    protected readonly ?string $altText;
    protected readonly ?string $titleText;
    protected readonly ?string $authorName;
    protected readonly ?string $authorUrl;
    protected readonly ?string $copyrightText;
    protected readonly ?string $licenseName;
    protected readonly ?string $licenseUrl;

    public function __construct(
        private readonly FrontMatter $matter,
    )
    {
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
            'altText' => $this->altText,
            'titleText' => $this->titleText,
            'authorName' => $this->authorName,
            'authorUrl' => $this->authorUrl,
            'copyrightText' => $this->copyrightText,
            'licenseName' => $this->licenseName,
            'licenseUrl' => $this->licenseUrl,
        ];
    }

    protected function makeAltText(): ?string
    {
        return null;
    }

    protected function makeTitleText(): ?string
    {
        return null;
    }

    protected function makeAuthorName(): ?string
    {
        return null;
    }

    protected function makeAuthorUrl(): ?string
    {
        return null;
    }

    protected function makeCopyrightText(): ?string
    {
        return null;
    }

    protected function makeLicenseName(): ?string
    {
        return null;
    }

    protected function makeLicenseUrl(): ?string
    {
        return null;
    }
}
