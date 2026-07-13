<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Hyde;
use Hyde\Console\Concerns\Command;
use Hyde\Foundation\Facades\Routes;
use Hyde\Framework\Actions\StaticPageBuilder;
use Hyde\Framework\Features\XmlGenerators\SitemapPage;
use Hyde\Pages\Concerns\HydePage;

use function sprintf;

/**
 * Run the build process for the sitemap.
 */
class BuildSitemapCommand extends Command
{
    /** @var string */
    protected $signature = 'build:sitemap';

    /** @var string */
    protected $description = 'Generate the sitemap.xml file';

    public function handle(): int
    {
        if (! Hyde::hasSiteUrl()) {
            $this->error('Cannot generate sitemap without a valid base URL');

            return Command::FAILURE;
        }

        $path = StaticPageBuilder::handle($this->getSitemapPage());

        $this->infoComment(sprintf('Created [%s]', Hyde::pathToRelative($path)));

        return Command::SUCCESS;
    }

    /** Get the registered sitemap page, falling back to a new instance when the route is not registered. */
    protected function getSitemapPage(): HydePage
    {
        return Routes::find(SitemapPage::routeKey())?->getPage() ?? new SitemapPage();
    }
}
