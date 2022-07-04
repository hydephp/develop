<?php

namespace Hyde\Framework\Services;

use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Framework\Modules\Routing\Router;
use Hyde\Framework\StaticPageBuilder;
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

    public function run(): void
    {
        $this->runBuildAction(BladePage::class);
        $this->runBuildAction(MarkdownPage::class);
        $this->runBuildAction(MarkdownPost::class);
        $this->runBuildAction(DocumentationPage::class);
    }

    protected function canRunBuildAction(array $collection, string $pageClass): bool
    {
        $name = $this->getModelPluralName($pageClass);

        if (sizeof($collection) < 1) {
            $this->line("No $name found. Skipping...\n");

            return false;
        }

        $this->comment("Creating $name...");

        return true;
    }

    /** @internal */
    protected function runBuildAction(string $pageClass): void
    {
        $collection = CollectionService::getSourceFileListForModel($pageClass);
        if ($this->canRunBuildAction($collection, $pageClass)) {
            $this->withProgressBar(
                $collection,
                $this->compileModel($pageClass)
            );
            $this->newLine(2);
        }
    }

    protected function compileModel(string $pageClass): callable
    {
        return function ($basename) use ($pageClass) {
            return (new StaticPageBuilder(
                DiscoveryService::getParserInstanceForModel(
                    $pageClass,
                    $basename
                )->get())
            )->__invoke();
        };
    }

    /** @internal */
    protected function getModelPluralName(string $pageClass): string
    {
        return preg_replace('/([a-z])([A-Z])/', '$1 $2', class_basename($pageClass)).'s';
    }
}
