<?php

namespace Hyde\Framework\Services;

use Hyde\Framework\Modules\Routing\Router;
use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Console\OutputStyle;

/**
 * Moves logic from the build command to a service.
 *
 * Handles the build loop which generates the static site.
 */
class BuildService
{
    use InteractsWithIO;

    protected Router $router;

    public function __construct(OutputStyle $output)
    {
        $this->output = $output;

        $this->router = Router::getInstance();
    }

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

    /** @internal */
    protected function getModelPluralName(string $model): string
    {
        return preg_replace('/([a-z])([A-Z])/', '$1 $2', class_basename($model)).'s';
    }
}
