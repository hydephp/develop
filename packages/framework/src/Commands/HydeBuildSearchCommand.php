<?php

namespace Hyde\Framework\Commands;

use Hyde\Framework\Actions\GeneratesDocumentationSearchIndexFile;
use Hyde\Framework\Hyde;
use Hyde\Framework\Services\CollectionService;
use LaravelZero\Framework\Commands\Command;

/**
 * Hyde Command to run the Build Process for the DocumentationSearchIndex.
 *
 * @todo Add configuration option to enable/disable this feature.
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\HydeBuildSearchCommandTest
 */
class HydeBuildSearchCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'build:search';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Generate the docs/search.json';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $actionTime = microtime(true);

        $this->comment('Generating documentation site search index...');
        $this->line('<fg=gray> > This will take an estimated '.round($this->guesstimateGenerationTime() / 1000).' seconds. Terminal may seem non-responsive.</>');
        GeneratesDocumentationSearchIndexFile::run();

        $this->line(' > Created <info>'.GeneratesDocumentationSearchIndexFile::$filePath.'</> in '.
            $this->getExecutionTimeInMs($actionTime)."ms\n");

        if (config('docs.create_search_page', true)) {
            $this->createSearchPage();
        }

        return 0;
    }

    protected function createSearchPage(): void
    {
        $actionTime = microtime(true);

        $this->comment('Generating search page...');
        file_put_contents(
            Hyde::path('_site/'.config('docs.output_directory', 'docs').'/search.html'),
            view('hyde::pages.documentation-search')->render()
        );

        $this->line(' > Created <info>_site/'.config('docs.output_directory', 'docs').'/search.html</> in '.
        $this->getExecutionTimeInMs($actionTime)."ms\n");
    }

    protected function guesstimateGenerationTime(): float
    {
        return count(CollectionService::getDocumentationPageList()) * 52.5;
    }

    protected function getExecutionTimeInMs(float $timeStart): string
    {
        return number_format(((microtime(true) - $timeStart) * 1000), 2);
    }
}
