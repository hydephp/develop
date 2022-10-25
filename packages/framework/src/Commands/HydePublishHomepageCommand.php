<?php

namespace Hyde\Framework\Commands;

use Hyde\Framework\Actions\PublishesHomepageView;
use Hyde\Framework\Concerns\Commands\AsksToRebuildSite;
use Hyde\Framework\Hyde;
use Hyde\Framework\Services\ChecksumService;
use Illuminate\Support\Facades\Artisan;
use LaravelZero\Framework\Commands\Command;

/**
 * Publish one of the default homepages.
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\HydePublishHomepageCommandTest
 */
class HydePublishHomepageCommand extends Command
{
    use AsksToRebuildSite;

    /** @var string */
    protected $signature = 'publish:homepage {homepage? : The name of the page to publish}
                                {--force : Overwrite any existing files}';

    /** @var string */
    protected $description = 'Publish one of the default homepages to index.blade.php.';

    protected string $selected;

    public function handle(): int
    {
        $this->selected = $this->argument('homepage') ?? $this->promptForHomepage();

        if (! $this->canExistingIndexFileBeOverwritten()) {
            $this->error('A modified index.blade.php file already exists. Use --force to overwrite.');

            return 409;
        }

        $returnValue = (new PublishesHomepageView(
            $this->selected
        ))->execute();

        if (is_numeric($returnValue)) {
            if ($returnValue == 404) {
                $this->error('Homepage '.$this->selected.' does not exist.');

                return 404;
            }
        }

        $this->line("<info>Published page</info> [<comment>$this->selected</comment>]");

        $this->askToRebuildSite();

        return 0;
    }

    protected function promptForHomepage(): string
    {
        /** @var string $choice */
        $choice = $this->choice(
            'Which homepage do you want to publish?',
            $this->formatPublishableChoices(),
            0
        );

        return $this->parseChoiceIntoKey($choice);
    }

    protected function formatPublishableChoices(): array
    {
        $keys = [];
        foreach (PublishesHomepageView::$homePages as $key => $value) {
            $keys[] = "<comment>$key</comment>: {$value['description']}";
        }

        return $keys;
    }

    protected function parseChoiceIntoKey(string $choice): string
    {
        return strstr(str_replace(['<comment>', '</comment>'], '', $choice), ':', true);
    }

    protected function canExistingIndexFileBeOverwritten(): bool
    {
        if (! file_exists(Hyde::getBladePagePath('index.blade.php')) || $this->option('force')) {
            return true;
        }

        return ChecksumService::checksumMatchesAny(ChecksumService::unixsumFile(
            Hyde::getBladePagePath('index.blade.php')
        )) || $this->option('force');
    }

    protected function askToRebuildSite(): void
    {
        if ($this->option('no-interaction')) {
            return;
        }

        if ($this->confirm('Would you like to rebuild the site?', 'Yes')) {
            $this->line('Okay, building site!');
            Artisan::call('build');
            $this->info('Site is built!');
        } else {
            $this->line('Okay, you can always run the build later!');
        }
    }
}
