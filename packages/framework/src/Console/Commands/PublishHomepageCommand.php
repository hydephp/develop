<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Console\Concerns\AsksToRebuildSite;
use Hyde\Framework\Features\Templates\Homepages;
use Hyde\Framework\Features\Templates\PublishableContract;
use Hyde\Framework\Services\ChecksumService;
use Hyde\Hyde;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use LaravelZero\Framework\Commands\Command;

/**
 * Publish one of the default homepages.
 *
 * @todo Refactor to use vendor:publish and to use code similar to {@see \Hyde\Console\Commands\PublishViewsCommand}
 *
 * @deprecated May be replaced by vendor:publish in the future.
 * @see \Hyde\Framework\Testing\Feature\Commands\PublishHomepageCommandTest
 */
class PublishHomepageCommand extends Command
{
    use AsksToRebuildSite;

    /** @var string */
    protected $signature = 'publish:homepage {homepage? : The name of the page to publish}
                                {--force : Overwrite any existing files}';

    /** @var string */
    protected $description = 'Publish one of the default homepages to index.blade.php.';

    protected array $options = [
        'welcome'=> [
            'name' => 'Welcome',
            'description' => 'The default welcome page.',
            'group' => 'hyde-welcome-page',
        ],
        'posts'=> [
            'name' => 'Posts Feed',
            'description' => 'A feed of your latest posts. Perfect for a blog site!',
            'group' => 'hyde-posts-page',
        ],
        'blank'=>  [
            'name' => 'Blank Starter',
            'description' => 'A blank Blade template with just the base layout.',
            'group' => 'hyde-blank-page',
        ]
    ];

    public function handle(): int
    {
        $selected = $this->parseSelection();

        if (! $this->canExistingFileBeOverwritten()) {
            $this->error('A modified index.blade.php file already exists. Use --force to overwrite.');

            return 409;
        }

        Artisan::call('vendor:publish', [
            '--tag' => $this->options[$selected]['group'] ?? $selected,
            '--force' => true, // Todo add force state dynamically depending on existing file state
        ], $this->output);

        // Todo only show if called command was successful
        $this->line("<info>Published page</info> [<comment>$selected</comment>]");

        $this->askToRebuildSite();

        return Command::SUCCESS;
    }

    protected function parseSelection(): string
    {
        return $this->argument('homepage') ?? $this->parseChoiceIntoKey($this->promptForHomepage());
    }

    protected function promptForHomepage(): string
    {
        return $this->choice(
            'Which homepage do you want to publish?',
            $this->formatPublishableChoices(),
            0
        );
    }

    protected function formatPublishableChoices(): array
    {
        return $this->getTemplateOptions()->map(function (array $option, string $key): string {
            return  "<comment>$key</comment>: {$option['description']}";
        })->values()->toArray();
    }

    protected function getTemplateOptions(): Collection
    {
        return new Collection($this->options);
    }

    protected function parseChoiceIntoKey(string $choice): string
    {
        return strstr(str_replace(['<comment>', '</comment>'], '', $choice), ':', true);
    }

    protected function canExistingFileBeOverwritten(): bool
    {
        if ($this->option('force')) {
            return true;
        }

        if (! file_exists(Hyde::getBladePagePath('index.blade.php'))) {
            return true;
        }

        return $this->isTheExistingFileADefaultOne();
    }

    protected function isTheExistingFileADefaultOne(): bool
    {
        return ChecksumService::checksumMatchesAny(ChecksumService::unixsumFile(
            Hyde::getBladePagePath('index.blade.php')
        ));
    }
}
