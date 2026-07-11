<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Foundation\Facades\Pages;
use Hyde\Framework\Actions\StaticPageBuilder;
use LaravelZero\Framework\Commands\Command;
use Hyde\Framework\Features\Documentation\DocumentationSearchPage;
use Hyde\Framework\Features\Documentation\DocumentationSearchIndex;
use Hyde\Framework\Features\Documentation\Versioning\DocumentationVersion;
use Hyde\Framework\Features\Documentation\Versioning\DocumentationVersions;

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
        if (DocumentationVersions::enabled()) {
            DocumentationVersions::all()->each(function (DocumentationVersion $version): void {
                $this->build($version);
            });
        } else {
            $this->build(null);
        }

        return Command::SUCCESS;
    }

    protected function build(?DocumentationVersion $version): void
    {
        StaticPageBuilder::handle(Pages::get(DocumentationSearchIndex::routeKey($version)) ?? new DocumentationSearchIndex($version));

        if (DocumentationSearchPage::enabled($version)) {
            StaticPageBuilder::handle(Pages::get(DocumentationSearchPage::routeKey($version)) ?? new DocumentationSearchPage($version));
        }
    }
}
