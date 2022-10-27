<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions\PostBuildTasks;

use Hyde\Framework\Concerns\InteractsWithDirectories;
use Hyde\Framework\Features\BuildTasks\BuildTask;
use Hyde\Framework\Services\DiscoveryService;
use Hyde\Framework\Services\DocumentationSearchService;

class GenerateSearch extends BuildTask
{
    use InteractsWithDirectories;

    public static string $description = 'Generating search index';

    public function run(): void
    {
        $expected = $this->guesstimateGenerationTime();
        if ($expected >= 1) {
            $this->line("<fg=gray>This will take an estimated $expected seconds. Terminal may seem non-responsive.</>");
        }

        DocumentationSearchService::generate();

        if (config('docs.create_search_page', true)) {
            $directory = DocumentationSearchService::generateSearchPage();

            $this->createdSiteFile("$directory/search.html");
        }
    }

    public function then(): void
    {
        $this->createdSiteFile(DocumentationSearchService::$filePath)->withExecutionTime();
    }

    /** @internal Estimated processing time per file in ms */
    public static float $guesstimationFactor = 52.5;

    protected function guesstimateGenerationTime(): int|float
    {
        return (int) round(count(DiscoveryService::getDocumentationPageFiles()) * static::$guesstimationFactor) / 1000;
    }
}
