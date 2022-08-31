<?php

namespace Hyde\Framework\Actions\PostBuildTasks;

use Hyde\Framework\Concerns\InteractsWithDirectories;
use Hyde\Framework\Contracts\AbstractBuildTask;
use Hyde\Framework\Services\DiscoveryService;
use Hyde\Framework\Services\DocumentationSearchService;

class GenerateSearch extends AbstractBuildTask
{
    use InteractsWithDirectories;

    public static string $description = 'Generating search index';

    public function run(): void
    {
        $expected = $this->guesstimateGenerationTime();
        if ($expected > 1) {
            $this->line("<fg=gray> > This will take an estimated $expected seconds. Terminal may seem non-responsive.</>");
        }

        DocumentationSearchService::generate();

        if (config('docs.create_search_page', true)) {
            $directory = DocumentationSearchService::generateSearchPage();

            $this->write(sprintf(
                "\n > Created <info>%s/search.html</info>",
                $directory
            ));
        }
    }

    public function then(): void
    {
        $this->writeln(sprintf("\n > Created <info>%s</info> in %s",
            DocumentationSearchService::$filePath,
            $this->getExecutionTime()
        ));
    }

    /** @internal Estimated processing time per file in ms */
    public static float $guesstimationFactor = 52.5;

    protected function guesstimateGenerationTime(): int|float
    {
        return (int) round(count(DiscoveryService::getDocumentationPageFiles()) * static::$guesstimationFactor) / 1000;
    }
}
