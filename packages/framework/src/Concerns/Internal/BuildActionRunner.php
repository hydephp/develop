<?php

namespace Hyde\Framework\Concerns\Internal;

use Hyde\Framework\Services\CollectionService;
use Hyde\Framework\Services\DiscoveryService;
use Hyde\Framework\StaticPageBuilder;

/**
 * Offloads build action logic for static site building commands.
 *
 * @deprecated Use BuildService instead.
 *
 * @see \Hyde\Framework\Commands\HydeBuildStaticSiteCommand
 *
 * @internal
 */
trait BuildActionRunner
{
    /** @internal */
    protected function canRunBuildAction(array $collection, string $name, ?string $verb = null): bool
    {
        if (sizeof($collection) < 1) {
            $this->line('No '.$name.' found. Skipping...');
            $this->newLine();

            return false;
        }

        $this->comment(($verb ?? 'Creating')." $name...");

        return true;
    }

    /** @internal */
    protected function runBuildAction(string $model): void
    {
        $collection = CollectionService::getSourceFileListForModel($model);
        $modelName = $this->getModelPluralName($model);
        if ($this->canRunBuildAction($collection, $modelName)) {
            $this->withProgressBar(
                $collection,
                function ($basename) use ($model) {
                    new StaticPageBuilder(
                        DiscoveryService::getParserInstanceForModel(
                            $model,
                            $basename
                        )->get(),
                        true
                    );
                }
            );
            $this->newLine(2);
        }
    }
}
