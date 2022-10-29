<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Blogging\Models;

use Stringable;

abstract class FeaturedImage implements Stringable
{
    protected readonly ?string $altText;
    protected readonly ?string $titleText;

    protected readonly ?string $authorName;
    protected readonly ?string $authorUrl;

    protected readonly ?string $copyrightText;
    protected readonly ?string $licenseName;
    protected readonly ?string $licenseUrl;

    public function __construct(?string $altText, ?string $titleText, ?string $authorName, ?string $authorUrl, ?string $copyrightText, ?string $licenseName, ?string $licenseUrl)
    {
        $this->altText = $altText;
        $this->titleText = $titleText;
        $this->authorName = $authorName;
        $this->authorUrl = $authorUrl;
        $this->copyrightText = $copyrightText;
        $this->licenseName = $licenseName;
        $this->licenseUrl = $licenseUrl;
    }

    public function __toString(): string
    {
        return $this->getSource();
    }

    /**
     * Get the source of the image, must be usable within the src attribute of an image tag.
     * @return string The image's url or path
     */
    abstract public function getSource(): string;

    abstract public function getContentLength(): int;

    public function getAltText(): ?string
    {
        return $this->altText;
    }

    public function getTitleText(): ?string
    {
        return $this->titleText;
    }

    public function getAuthorName(): ?string
    {
        return $this->authorName;
    }

    public function getAuthorUrl(): ?string
    {
        return $this->authorUrl;
    }

    public function getCopyrightText(): ?string
    {
        return $this->copyrightText;
    }

    public function getLicenseName(): ?string
    {
        return $this->licenseName;
    }

    public function getLicenseUrl(): ?string
    {
        return $this->licenseUrl;
    }

    public function hasAltText(): bool
    {
        return $this->altText !== null;
    }

    public function hasTitleText(): bool
    {
        return $this->titleText !== null;
    }

    public function hasAuthorName(): bool
    {
        return $this->authorName !== null;
    }

    public function hasAuthorUrl(): bool
    {
        return $this->authorUrl !== null;
    }

    public function hasCopyrightText(): bool
    {
        return $this->copyrightText !== null;
    }

    public function hasLicenseName(): bool
    {
        return $this->licenseName !== null;
    }

    public function hasLicenseUrl(): bool
    {
        return $this->licenseUrl !== null;
    }
}
