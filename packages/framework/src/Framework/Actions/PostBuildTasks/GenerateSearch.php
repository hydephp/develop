<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions\PostBuildTasks;

use Hyde\Framework\Concerns\InteractsWithDirectories;
use Hyde\Framework\Features\BuildTasks\PostBuildTask;
use Hyde\Framework\Features\Documentation\DocumentationSearchPage;
use Hyde\Framework\Actions\GeneratesDocumentationSearchIndex;

/** @deprecated */
class GenerateSearch extends PostBuildTask
{
    use InteractsWithDirectories;

    public static string $message = 'Generating search index';

    protected string $path;

    public function handle(): void
    {
        $this->path = GeneratesDocumentationSearchIndex::handle();

        if (DocumentationSearchPage::enabled()) {
            $this->createdSiteFile(DocumentationSearchPage::generate());
        }
    }

    public function printFinishMessage(): void
    {
        $this->createdSiteFile($this->path)->withExecutionTime();
    }
}
