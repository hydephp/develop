<?php

declare(strict_types=1);

namespace Hyde\Framework\Services;

use Hyde\Hyde;
use Illuminate\Support\Str;
use function str_contains;

/**
 * Handles the retrieval of core asset files. Commonly used through the Asset facade.
 *
 * This class is loaded into the service container, making it easy to access and modify.
 *
 * @see \Hyde\Facades\Asset
 */
class AssetService
{
    /**
     * The default HydeFront version to load.
     *
     * @property string $version HydeFront SemVer Tag
     */
    public string $version = 'v2.0';

    public function version(): string
    {
        return $this->version;
    }

    public function constructCdnPath(string $file): string
    {
        return 'https://cdn.jsdelivr.net/npm/hydefront@'.$this->version().'/dist/'.$file;
    }

    /**
     * Alias for constructCdnPath.
     */
    public function cdnLink(string $file): string
    {
        return $this->constructCdnPath($file);
    }

    public function mediaLink(string $file): string
    {
        return Hyde::relativeLink("media/$file").$this->getCacheBustKey($file);
    }

    public function hasMediaFile(string $file): bool
    {
        return file_exists(Hyde::path('_media').'/'.$file);
    }

    public function injectTailwindConfig(): string
    {
        $config = Str::between(file_get_contents(Hyde::path('tailwind.config.js')), '{', '}');

        if (str_contains($config, 'plugins: [')) {
            $tokens = explode('plugins: [', $config, 2);
            $tokens[1] = Str::after($tokens[1], ']');
            $config = implode('', $tokens);
        }

        return preg_replace('/\s+/', ' ', "/* tailwind.config.js */ \n".rtrim($config, ",\n\r"));
    }

    protected function getCacheBustKey(string $file): string
    {
        if (! config('hyde.cache_busting', true)) {
            return '';
        }

        return '?v='.md5_file(Hyde::path("_media/$file"));
    }
}
