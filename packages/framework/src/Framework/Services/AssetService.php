<?php

declare(strict_types=1);

namespace Hyde\Framework\Services;

use Hyde\Hyde;
use Hyde\Facades\Config;
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
    /** @var string The default HydeFront version to load. This constant is set to match the styles used for the installed framework version. */
    public final const HYDEFRONT_VERSION = 'v2.0';

    /** @var string The default HydeFront CDN path pattern. The Blade-style placeholders are replaced with the proper values. */
    public final const HYDEFRONT_CDN_URL = 'https://cdn.jsdelivr.net/npm/hydefront@{{ $version }}/dist/{{ $file }}';

    /**
     * The HydeFront version to load.
     *
     * @var string HydeFront SemVer Tag
     */
    public string $version = self::HYDEFRONT_VERSION;

    protected string $hydefrontUrl = self::HYDEFRONT_CDN_URL;

    public function __construct()
    {
        $this->version = Config::getString('hyde.hydefront_version', self::HYDEFRONT_VERSION);
        $this->hydefrontUrl = Config::getString('hyde.hydefront_url', self::HYDEFRONT_CDN_URL);
    }

    public function version(): string
    {
        return $this->version;
    }

    public function cdnLink(string $file): string
    {
        return $this->constructCdnPath($file);
    }

    public function mediaLink(string $file): string
    {
        return Hyde::mediaLink("$file").$this->getCacheBustKey($file);
    }

    public function hasMediaFile(string $file): bool
    {
        return file_exists(Hyde::mediaPath().'/'.$file);
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

    protected function constructCdnPath(string $file): string
    {
        return str_replace(
            ['{{ $version }}', '{{ $file }}'], [$this->version(), $file],
            $this->hydefrontUrl
        );
    }

    protected function getCacheBustKey(string $file): string
    {
        if (! Config::getBool('hyde.cache_busting', true)) {
            return '';
        }

        return '?v='.md5_file(Hyde::mediaPath("$file"));
    }
}
