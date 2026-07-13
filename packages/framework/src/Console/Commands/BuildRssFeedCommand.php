<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Hyde;
use Hyde\Console\Concerns\Command;
use Hyde\Foundation\Facades\Routes;
use Hyde\Framework\Actions\StaticPageBuilder;
use Hyde\Framework\Features\XmlGenerators\RssFeedPage;
use Hyde\Pages\Concerns\HydePage;

use function sprintf;

/**
 * Run the build process for the RSS feed.
 */
class BuildRssFeedCommand extends Command
{
    /** @var string */
    protected $signature = 'build:rss';

    /** @var string */
    protected $description = 'Generate the RSS feed';

    public function handle(): int
    {
        $path = StaticPageBuilder::handle($this->getFeedPage());

        $this->infoComment(sprintf('Created [%s]', Hyde::pathToRelative($path)));

        return Command::SUCCESS;
    }

    /** Get the registered RSS feed page, falling back to a new instance when the route is not registered. */
    protected function getFeedPage(): HydePage
    {
        return Routes::find(RssFeedPage::routeKey())?->getPage() ?? new RssFeedPage();
    }
}
