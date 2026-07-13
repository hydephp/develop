<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Hyde;
use Hyde\Console\Concerns\Command;
use Hyde\Facades\Config;
use Hyde\Facades\Features;
use Hyde\Foundation\Facades\Routes;
use Hyde\Framework\Actions\StaticPageBuilder;
use Hyde\Framework\Features\XmlGenerators\RssFeedPage;
use Hyde\Pages\MarkdownPost;

use function count;
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
        $page = Routes::find(RssFeedPage::routeKey())?->getPage();

        if ($page === null) {
            $this->error($this->getSkipReason());

            return Command::FAILURE;
        }

        $path = StaticPageBuilder::handle($page);

        $this->infoComment(sprintf('Created [%s]', Hyde::pathToRelative($path)));

        return Command::SUCCESS;
    }

    /** Explain why the RSS feed route is not registered, mirroring the conditions of {@see \Hyde\Facades\Features::hasRss()}. */
    protected function getSkipReason(): string
    {
        if (! Hyde::hasSiteUrl()) {
            return 'Cannot generate an RSS feed without a valid base URL';
        }

        if (! Config::getBool('hyde.rss.enabled', true)) {
            return 'Cannot generate the RSS feed as it is disabled in the configuration';
        }

        if (! Features::hasMarkdownPosts() || count(MarkdownPost::files()) === 0) {
            return 'Cannot generate an RSS feed without any Markdown posts';
        }

        return 'Cannot generate the RSS feed as the SimpleXML extension is not installed';
    }
}
