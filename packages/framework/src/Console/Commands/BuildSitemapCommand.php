<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Hyde;
use Hyde\Console\Concerns\Command;
use Hyde\Facades\Config;
use Hyde\Foundation\Facades\Routes;
use Hyde\Framework\Actions\StaticPageBuilder;
use Hyde\Framework\Features\XmlGenerators\SitemapPage;

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
        $page = Routes::find(SitemapPage::routeKey())?->getPage();

        if ($page === null) {
            $this->error($this->getSkipReason());

            return Command::FAILURE;
        }

        $path = StaticPageBuilder::handle($page);

        $this->infoComment(sprintf('Created [%s]', Hyde::pathToRelative($path)));

        return Command::SUCCESS;
    }

    /** Explain why the sitemap route is not registered, mirroring the conditions of {@see \Hyde\Facades\Features::hasSitemap()}. */
    protected function getSkipReason(): string
    {
        if (! Hyde::hasSiteUrl()) {
            return 'Cannot generate sitemap without a valid base URL';
        }

        if (! Config::getBool('hyde.generate_sitemap', true)) {
            return 'Cannot generate the sitemap as it is disabled in the configuration';
        }

        return 'Cannot generate the sitemap as the SimpleXML extension is not installed';
    }
}
