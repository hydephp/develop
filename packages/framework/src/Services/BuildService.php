<?php

namespace Hyde\Framework\Services;

use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPage;
use Hyde\Framework\Models\Pages\MarkdownPost;
use Hyde\Framework\Modules\Routing\RouteContract as Route;
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
        $this->compilePages(BladePage::class);
        $this->compilePages(MarkdownPage::class);
        $this->compilePages(MarkdownPost::class);
        $this->compilePages(DocumentationPage::class);
    }

    protected function canRunBuildAction(\Countable $collection, string $pageClass): bool
    {
        $name = $this->getModelPluralName($pageClass);

        if (sizeof($collection) < 1) {
            $this->line("No $name found. Skipping...\n");

            return false;
        }

        $this->comment("Creating $name...");

        return true;
    }

    protected function compilePages(string $pageClass): void
    {
        $collection = $this->router->getRoutesForModel($pageClass);

        if ($this->canRunBuildAction($collection, $pageClass)) {
            $this->withProgressBar(
                $collection, $this->compileRoute()
            );
            $this->newLine(2);
        }
    }

    protected function compileRoute(): \Closure
    {
        return function (Route $route) {
            return (new StaticPageBuilder($route->getSourceModel()))->__invoke();
        };
    }

    protected function getModelPluralName(string $pageClass): string
    {
        return preg_replace('/([a-z])([A-Z])/', '$1 $2', class_basename($pageClass)).'s';
    }
}
