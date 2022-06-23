<?php

namespace Hyde\Framework\Concerns\Internal;

use Hyde\Framework\Contracts\AssetServiceContract;

/**
 * Offloads asset related methods for the Hyde Facade.
 *
 * @deprecated version 0.41.x - Use the Asset facade instead.
 * @see \Hyde\Framework\Hyde
 */
trait AssetManager
{
    /**
     * Get the asset service instance.
     *
     * @deprecated version 0.41.x - Use the Asset facade instead.
     *
     * @return \Hyde\Framework\Contracts\AssetServiceContract
     */
    public static function assetManager(): AssetServiceContract
    {
        return app(AssetServiceContract::class);
    }

    /**
     * Return the Hyde stylesheet.
     *
     * @deprecated version 0.41.x - Use the Asset facade instead.
     */
    public static function styles(): string
    {
        return static::assetManager()->stylePath();
    }

    /**
     * Return the Hyde scripts.
     *
     * @deprecated version 0.41.x - Use the Asset facade instead.
     */
    public static function scripts(): string
    {
        return static::assetManager()->scriptPath();
    }
}
