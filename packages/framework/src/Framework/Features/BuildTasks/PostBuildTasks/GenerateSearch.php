<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\BuildTasks\PostBuildTasks;

use Hyde\Framework\Concerns\InteractsWithDirectories;
use Hyde\Framework\Features\BuildTasks\BuildTask;
use Hyde\Framework\Features\Documentation\DocumentationSearchPage;
use Hyde\Framework\Services\DocumentationSearchService;

class GenerateSearch extends BuildTask
{
    use InteractsWithDirectories;

    public static string $description = 'Generating search index';

    public function run(): void
    {
        DocumentationSearchService::generate();

        if (DocumentationSearchPage::enabled()) {
            $directory = DocumentationSearchService::generateSearchPage();

            $this->createdSiteFile("$directory/search.html");
        }
    }

    public function then(): void
    {
        $this->createdSiteFile(DocumentationSearchService::getFilePath())->withExecutionTime();
    }
}
