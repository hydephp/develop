<?php

namespace Hyde\Framework\Facades;

use Hyde\Framework\Contracts\AssetServiceContract;
use Illuminate\Support\Facades\Facade;

/**
 * @see \Hyde\Framework\Services\AssetService
 */
class Asset extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return AssetServiceContract::class;
    }
}