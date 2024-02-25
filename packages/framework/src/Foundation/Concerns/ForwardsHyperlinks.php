<?php

declare(strict_types=1);

namespace Hyde\Foundation\Concerns;

use Hyde\Support\Models\Route;

/**
 * @internal Single-use trait for the HydeKernel class.
 *
 * @see \Hyde\Foundation\HydeKernel
 */
trait ForwardsHyperlinks
{
    public function formatLink(string $destination): string
    {
        return $this->hyperlinks->formatLink($destination);
    }

    public function relativeLink(string $destination): string
    {
        return $this->hyperlinks->relativeLink($destination);
    }

    public function mediaLink(string $destination, bool $validate = false): string
    {
        return $this->hyperlinks->mediaLink($destination, $validate);
    }

    public function asset(string $name, bool $preferQualifiedUrl = false): string
    {
        return $this->hyperlinks->asset($name, $preferQualifiedUrl);
    }

    public function url(string $path = ''): string
    {
        return $this->hyperlinks->url($path);
    }

    public function route(string $key): ?Route
    {
        return $this->hyperlinks->route($key);
    }

    public function hasSiteUrl(): bool
    {
        return $this->hyperlinks->hasSiteUrl();
    }
}
