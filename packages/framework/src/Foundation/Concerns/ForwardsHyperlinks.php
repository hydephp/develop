<?php

namespace Hyde\Framework\Foundation\Concerns;

/**
 * @internal Single-use trait for the HydeKernel class.
 *
 * @see \Hyde\Framework\HydeKernel
 */
trait ForwardsHyperlinks
{
    public function formatHtmlPath(string $destination): string
    {
        return $this->hyperlinks->formatHtmlPath($destination);
    }

    public function relativeLink(string $destination): string
    {
        return $this->hyperlinks->relativeLink($destination);
    }

    public function image(string $name, bool $preferQualifiedUrl = false): string
    {
        return $this->hyperlinks->image($name, $preferQualifiedUrl);
    }

    public function hasSiteUrl(): bool
    {
        return $this->hyperlinks->hasSiteUrl();
    }

    public function url(string $path = '', ?string $default = null): string
    {
        return $this->hyperlinks->url($path, $default);
    }
}
