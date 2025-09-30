<?php

declare(strict_types=1);

namespace Hyde\Framework\Services;

use Hyde\Hyde;
use Hyde\Pages\InMemoryPage;
use Hyde\Foundation\Facades\Routes;
use Hyde\Foundation\Kernel\RouteCollection;
use Hyde\Framework\Actions\StaticPageBuilder;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Support\Models\Route;
use Hyde\Console\StyledProgressOutput;
use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Console\OutputStyle;

use function class_basename;
use function preg_replace;
use function collect;

/**
 * Moves logic from the build command to a service.
 *
 * Handles the build loop which generates the static site.
 *
 * @see \Hyde\Console\Commands\BuildSiteCommand
 */
class BuildService
{
    use InteractsWithIO;

    protected RouteCollection $router;
    protected ?StyledProgressOutput $progressOutput = null;

    public function __construct(OutputStyle $output)
    {
        $this->output = $output;

        $this->router = Hyde::routes();
    }

    public function compileStaticPages(): void
    {
        $pageTypes = $this->getPageTypes();

        // Initialize styled progress output
        $this->progressOutput = new StyledProgressOutput();

        // Add all stages
        foreach ($pageTypes as $index => $pageClass) {
            $collection = Routes::getRoutes($pageClass);
            $icon = $this->getClassIcon($pageClass);
            $this->progressOutput->addStage(
                "Creating {$this->getClassPluralName($pageClass)}",
                $icon,
                $collection->count()
            );
        }

        // Process each page type
        collect($pageTypes)->each(function (string $pageClass, int $index): void {
            $this->compilePagesForClass($pageClass, $index);
        });

        // Show summary
        $totalFiles = Hyde::routes()->count();
        $this->newLine();
        $this->progressOutput->renderSummary($totalFiles);
    }

    /**
     * @param  class-string<\Hyde\Pages\Concerns\HydePage>  $pageClass
     */
    protected function compilePagesForClass(string $pageClass, int $stageIndex): void
    {
        $collection = Routes::getRoutes($pageClass);

        $this->progressOutput->startStage($stageIndex);

        foreach ($collection as $route) {
            StaticPageBuilder::handle($route->getPage());
            $this->progressOutput->advanceStage();
        }

        $this->progressOutput->completeStage();
    }

    protected function getClassPluralName(string $pageClass): string
    {
        if ($pageClass === InMemoryPage::class) {
            return 'Dynamic Pages';
        }

        return preg_replace('/([a-z])([A-Z])/', '$1 $2', class_basename($pageClass)).'s';
    }

    protected function getClassIcon(string $pageClass): string
    {
        $className = class_basename($pageClass);

        return match ($className) {
            'BladePage' => '<span class="text-blue-500">📄</span>',
            'MarkdownPage' => '<span class="text-blue-500">📝</span>',
            'MarkdownPost' => '<span class="text-blue-500">📰</span>',
            'DocumentationPage' => '<span class="text-blue-500">📚</span>',
            'InMemoryPage' => '<span class="text-blue-500">⚡</span>',
            default => '<span class="text-blue-500">📄</span>',
        };
    }

    /** @return array<class-string<\Hyde\Pages\Concerns\HydePage>> */
    protected function getPageTypes(): array
    {
        return Hyde::pages()->map(function (HydePage $page): string {
            if ($page instanceof InMemoryPage) {
                return InMemoryPage::class;
            }

            return $page::class;
        })->unique()->values()->toArray();
    }
}
