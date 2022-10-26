<?php

declare(strict_types=1);

namespace Hyde\Framework\Helpers;

use Hyde\Framework\Services\AssetService;
use Illuminate\Support\Facades\Facade;

/**
 * @see \Hyde\Framework\Services\AssetService
 *
 * @method static string version()
 * @method static string stylePath()
 * @method static string constructCdnPath(string $file)
 * @method static string cdnLink(string $file)
 * @method static string mediaLink(string $file)
 * @method static bool hasMediaFile(string $file)
 */
class Asset extends Facade
{
    /** @psalm-return AssetService::class */
    protected static function getFacadeAccessor(): string
    {
        return AssetService::class;
    }
}
