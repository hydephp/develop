<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Hyde;
use Hyde\Console\Concerns\Command;
use Hyde\Foundation\Facades\Routes;
use Hyde\Framework\Actions\StaticPageBuilder;
use Hyde\Framework\Features\GeneratedFiles\GeneratedFileRegistry;

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
        $page = Routes::find(GeneratedFileRegistry::SITEMAP)?->getPage();

        if ($page === null) {
            $this->error('Cannot generate the sitemap as the feature is not enabled');

            return Command::FAILURE;
        }

        $path = StaticPageBuilder::handle($page);

        $this->infoComment(sprintf('Created [%s]', Hyde::pathToRelative($path)));

        return Command::SUCCESS;
    }
}
