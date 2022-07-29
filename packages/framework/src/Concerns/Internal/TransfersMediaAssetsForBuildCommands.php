<?php

namespace Hyde\Framework\Concerns\Internal;

use Hyde\Framework\Concerns\InteractsWithDirectories;
use Hyde\Framework\Hyde;
use Hyde\Framework\Services\DiscoveryService;

/**
 * Transfer all media assets to the build directory.
 *
 * @see \Hyde\Framework\Commands\HydeBuildStaticSiteCommand
 * @see \Hyde\Framework\Commands\HydeRebuildStaticSiteCommand
 * @deprecated Use BuildService instead
 */
trait TransfersMediaAssetsForBuildCommands
{
    use InteractsWithDirectories;

    /** @deprecated */
    protected function transferMediaAssets(): void
    {
        $this->needsDirectory(Hyde::getSiteOutputPath('media'));

        $collection = DiscoveryService::getMediaAssetFiles();
        if ($this->canRunBuildAction($collection, 'Media Assets', 'Transferring')) {
            $this->withProgressBar(
                $collection,
                function ($filepath) {
                    copy($filepath, Hyde::getSiteOutputPath('media/'.basename($filepath)));
                }
            );
            $this->newLine(2);
        }
    }

    protected function canRunBuildAction(): bool
    {
        return true;
    }
}
