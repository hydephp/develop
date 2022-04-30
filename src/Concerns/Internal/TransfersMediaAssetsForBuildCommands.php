<?php

namespace Hyde\Framework\Concerns\Internal;

use Hyde\Framework\Hyde;
use Hyde\Framework\Services\CollectionService;

/**
 * Transfer all media assets to the build directory.
 *
 * @see \Hyde\Framework\Commands\HydeBuildStaticSiteCommand
 * @see \Hyde\Framework\Commands\HydeRebuildStaticSiteCommand
 *
 * @internal
 */
trait TransfersMediaAssetsForBuildCommands
{
    use BuildActionRunner;

    /** @internal */
    protected function transferMediaAssets(): void
    {
        $collection = CollectionService::getMediaAssetFiles();
        if ($this->canRunBuildAction($collection, 'Media Assets', 'Transferring')) {
            $this->withProgressBar(
                $collection,
                function ($filepath) {
                    copy($filepath, Hyde::path('_site/media/'.basename($filepath)));
                }
            );
            $this->newLine(2);
        }
    }
}
