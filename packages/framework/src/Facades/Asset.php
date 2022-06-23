<?php

namespace Hyde\Framework\Facades;

use Hyde\Framework\Contracts\AssetServiceContract;
use Illuminate\Support\Facades\Facade;

/**
 * @see \Hyde\Framework\Services\AssetService
 *
 * @method static string version()
 * @method static string stylePath()
 * @method static string scriptPath()
 * @method static string constructCdnPath(string $file)
 * @method static bool hasMediaFile(string $file)
 * @method static string cdnLink(string $file)
 */
class Asset extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return AssetServiceContract::class;
    }
}