<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Facades\Localization;
use Hyde\Foundation\Facades\Pages;
use Hyde\Framework\Actions\StaticPageBuilder;
use Hyde\Pages\Concerns\HydePage;
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
        $this->buildPage(Pages::get(DocumentationSearchIndex::routeKey($version)) ?? new DocumentationSearchIndex($version));

        if (DocumentationSearchPage::enabled($version)) {
            $this->buildPage(Pages::get(DocumentationSearchPage::routeKey($version)) ?? new DocumentationSearchPage($version));
        }
    }

    /**
     * Build the page, once for each language when the site is localized.
     *
     * The page collection holds one page per source, as the languages are variants of its
     * route, so this command has to fan them out the same way the route collection does,
     * or it would write the unlocalized path that a localized site never serves.
     */
    protected function buildPage(HydePage $page): void
    {
        foreach (Localization::enabled() ? Localization::languages() : [null] as $language) {
            StaticPageBuilder::handle($language === null ? $page : $page->withLanguage($language));
        }
    }
}
