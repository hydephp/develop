<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Console\Commands\Interfaces\CommandHandleInterface;
use Hyde\Facades\Features;
use Hyde\Framework\Features\BuildTasks\PostBuildTasks\GenerateRssFeed;
use Hyde\Framework\Features\BuildTasks\PostBuildTasks\GenerateSearch;
use Hyde\Framework\Features\BuildTasks\PostBuildTasks\GenerateSitemap;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Framework\Services\BuildService;
use Hyde\Framework\Services\BuildTaskService;
use Hyde\Framework\Services\DiscoveryService;
use Hyde\Hyde;
use Hyde\Pages\MarkdownPage;
use Hyde\PublicationHelper;
use Illuminate\Support\Facades\Config;
use LaravelZero\Framework\Commands\Command;
use Rgasch\Collection\Collection;

/**
 * Hyde Command to run the Build Process.
 *
 * @see \Hyde\Framework\Testing\Feature\StaticSiteServiceTest
 */
class BuildPublicationsCommand extends Command implements CommandHandleInterface
{
    /** @var string */
    protected $signature = 'build:publications';

    /** @var string */
    protected $description = 'Build the site publications';

    protected BuildService $service;

    public function handle(): int
    {
        $this->build();

        return Command::SUCCESS;
    }

    // Warning: This is extremely hacky ...
    protected function build(): void
    {
        $pubTypes = PublicationHelper::getPublicationTypes();
        foreach ($pubTypes as $dir => $pubType) {
            $targetDirectory = "_site/$dir";
            @mkdir($targetDirectory);
            $publications = PublicationHelper::getPublicationsForPubType($pubType);
            $this->info("Building [$pubType->name] into [$targetDirectory] ...");
            $this->buildDetailPages($targetDirectory, $pubType, $publications);
            $this->buildListPage($targetDirectory, $pubType, $publications);
        }
    }

    // TODO: Is detail page the right name?
    protected function buildDetailPages(string $targetDirectory, PublicationType $pubType, Collection $publications): void
    {
        $template = $pubType->detailTemplate;

        // Mock a page
        $page = new MarkdownPage($template);
        view()->share('page', $page);
        view()->share('currentPage', $template);
        view()->share('currentRoute', $page->getRoute());

        // TODO this should not be in the hyde namespace as user is expected to implement this right?
        $detailTemplate = 'hyde::pubtypes.'.$template;
        foreach ($publications as $publication) {
            $slug = $publication->matter->__slug;
            $this->info("  Building [$slug] ...");
            $html = view('hyde::layouts.pubtype')->with(['component' => $detailTemplate, 'publication' => $publication])->render();
            file_put_contents("$targetDirectory/{$slug}.html", $html);
        }
    }

    // TODO: Move to post build task?
    protected function buildListPage(string $targetDirectory, PublicationType $pubType, Collection $publications): void
    {
        $template = 'hyde::pubtypes.'.$pubType->listTemplate;
        $this->info('  Building list page ...');
        $html = view($template)->with('publications', $publications)->render();
        file_put_contents("$targetDirectory/index.html", $html);
    }
}
