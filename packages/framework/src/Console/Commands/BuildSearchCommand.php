<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Pages\Concerns\HydePage;
use Hyde\Foundation\Facades\Pages;
use Hyde\Framework\Actions\StaticPageBuilder;
use LaravelZero\Framework\Commands\Command;
use Hyde\Framework\Actions\GeneratesDocumentationSearchIndex;
use Hyde\Framework\Features\Documentation\DocumentationSearchPage;

/**
 * Run the build process for the documentation search index.
 */
class BuildSearchCommand extends Command
{
    /** @var string */
    protected $signature = 'build:search';

    /** @var string */
    protected $description = 'Generate the documentation search index';

    public function handle(): int
    {
        StaticPageBuilder::handle($this->getPageFromKernel() ?? GeneratesDocumentationSearchIndex::makePage());

        if (DocumentationSearchPage::enabled()) {
            DocumentationSearchPage::generate();
        }

        return Command::SUCCESS;
    }

    protected function getPageFromKernel(): ?HydePage
    {
        return Pages::get('search.json');
    }
}
