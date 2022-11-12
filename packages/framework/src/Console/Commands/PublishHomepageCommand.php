<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Framework\Actions\PublishesHomepageView;
use Hyde\Framework\Features\Templates\Homepages\BlankHomepageTemplate;
use Hyde\Framework\Features\Templates\Homepages\PostsFeedHomepageTemplate;
use Hyde\Framework\Features\Templates\Homepages\WelcomeHomepageTemplate;
use Hyde\Framework\Services\ChecksumService;
use Hyde\Hyde;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use function array_key_exists;

/**
 * Publish one of the default homepages.
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\PublishHomepageCommandTest
 */
class PublishHomepageCommand extends Command
{
    /** @var string */
    protected $signature = 'publish:homepage {homepage? : The name of the page to publish}
                                {--force : Overwrite any existing files}';

    /** @var string */
    protected $description = 'Publish one of the default homepages to index.blade.php.';

    public function handle(): int
    {
        $selected = $this->parseSelection();

        if (! array_key_exists($selected, $this->getTemplateOptions())) {
            $this->error("Homepage $selected does not exist.");
            return 404;
        }

        if (! $this->canExistingFileBeOverwritten()) {
            $this->error('A modified index.blade.php file already exists. Use --force to overwrite.');

            return 409;
        }

        /** @var \Hyde\Framework\Features\Templates\PublishableContract $template */
        $template = $this->getTemplateClasses()[$selected];

        $returnValue = $template::publish(true);

        if (! $returnValue) {
            $this->error('The homepage was not published.');
            return 500;
        }

        $this->line("<info>Published page</info> [<comment>$selected</comment>]");

        $this->askToRebuildSite();

        return Command::SUCCESS;
    }

    protected function parseSelection(): string
    {
        return $this->argument('homepage') ?? $this->promptForHomepage();
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
        foreach ($this->getTemplateOptions() as $key => $value) {
            $keys[] = "<comment>$key</comment>: {$value['description']}";
        }

        return $keys;
    }

    protected function getTemplateOptions(): array
    {
        return collect([
            BlankHomepageTemplate::class,
            PostsFeedHomepageTemplate::class,
            WelcomeHomepageTemplate::class,
        ])->mapWithKeys(fn(string $page): array => $this->getPublishableData($page))
            ->toArray();
    }

    protected function getTemplateClasses(): array
    {
        return [
            'blank' => BlankHomepageTemplate::class,
            'posts' => PostsFeedHomepageTemplate::class,
            'welcome' => WelcomeHomepageTemplate::class,
        ];
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

    /** @param class-string<\Hyde\Framework\Features\Templates\PublishableContract> $page */
    protected function getPublishableData(string $page): array
    {
        return [
            Str::before(Str::kebab($page::getTitle()), '-') => [
                'name' => $page::getTitle(),
                'description' => $page::getDescription(),
            ]
        ];
    }
}
