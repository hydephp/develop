<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions\PostBuildTasks;

use Hyde\Framework\Concerns\InteractsWithDirectories;
use Hyde\Framework\Features\BuildTasks\PostBuildTask;
use Hyde\Framework\Features\Documentation\DocumentationSearchPage;
use Hyde\Framework\Services\DocumentationSearchService;

class GenerateSearch extends PostBuildTask
{
    use InteractsWithDirectories;

    public static string $message = 'Generating search index';

    public function handle(): void
    {
        DocumentationSearchService::generate();

        if (DocumentationSearchPage::enabled()) {
            $this->createdSiteFile(DocumentationSearchPage::generate());
        }
    }

    public function printFinishMessage(): void
    {
        $this->createdSiteFile(DocumentationSearchService::getFilePath())->withExecutionTime();
    }
}
