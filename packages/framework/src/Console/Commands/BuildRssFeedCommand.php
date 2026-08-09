<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Hyde;
use Hyde\Console\Concerns\Command;
use Hyde\Foundation\Facades\Routes;
use Hyde\Framework\Actions\StaticPageBuilder;
use Hyde\Framework\Features\XmlGenerators\RssFeedGenerator;

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
        $page = Routes::find(RssFeedGenerator::getFilename())?->getPage();

        if ($page === null) {
            $this->error('Cannot generate the RSS feed as the feature is not enabled');

            return Command::FAILURE;
        }

        $path = StaticPageBuilder::handle($page);

        $this->infoComment(sprintf('Created [%s]', Hyde::pathToRelative($path)));

        return Command::SUCCESS;
    }
}
