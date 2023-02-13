<?php

declare(strict_types=1);

namespace Hyde\Facades;

use Hyde\Framework\Services\AssetService;
use Illuminate\Support\Facades\Facade;

/**
 * Handles the retrieval of core asset files, either from the HydeFront CDN or from the local media folder.
 *
 * @see \Hyde\Framework\Services\AssetService
 *
 * @method static string version()
 * @method static string stylePath()
 * @method static string constructCdnPath(string $file)
 * @method static string cdnLink(string $file)
 * @method static string mediaLink(string $file)
 * @method static bool hasMediaFile(string $file)
 * @method static string injectTailwindConfig()
 */
class Asset extends Facade
{
    /** @psalm-return AssetService::class */
    protected static function getFacadeAccessor(): string
    {
        return AssetService::class;
    }
}
