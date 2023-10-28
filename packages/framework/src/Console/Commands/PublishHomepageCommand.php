<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Pages\BladePage;
use Illuminate\Support\Str;
use Hyde\Console\Concerns\Command;
use Hyde\Console\Concerns\AsksToRebuildSite;
use Hyde\Framework\Services\ViewDiffService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Collection;
use Hyde\Console\Concerns\BetterChoiceHelper;

use function Hyde\unixsum_file;
use function array_key_exists;
use function file_exists;
use function str_replace;
use function strip_tags;
use function strstr;

/**
 * Publish one of the default homepages.
 */
class PublishHomepageCommand extends Command
{
    use AsksToRebuildSite;
    use BetterChoiceHelper;

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
        ],
    ];

    public function handle(): int
    {
        $selected = $this->parseSelection();

        if (! $this->canExistingFileBeOverwritten()) {
            $this->error('A modified index.blade.php file already exists. Use --force to overwrite.');

            return 409;
        }

        $tagExists = array_key_exists($selected, $this->options);

        Artisan::call('vendor:publish', [
            '--tag' => $this->options[$selected]['group'] ?? $selected,
            '--force' => true, // Todo add force state dynamically depending on existing file state
        ], ! $tagExists ? $this->output : null);

        if ($tagExists) {
            $this->infoComment("Published page [$selected]");

            $this->askToRebuildSite();
        }

        return $tagExists ? Command::SUCCESS : 404;
    }

    protected function parseSelection(): string
    {
        return $this->argument('homepage') ?? $this->parseChoiceIntoKey($this->promptForHomepage());
    }

    protected function promptForHomepage(): string
    {
        return $this->betterChoice(
            'Which homepage do you want to publish?',
            $this->formatPublishableChoices(),
            0,
            displayDefaultUsing: function (string $default): string {
                return strip_tags(Str::before($default, ':'));
            }
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

        if (! file_exists(BladePage::path('index.blade.php'))) {
            return true;
        }

        return $this->isTheExistingFileADefaultOne();
    }

    protected function isTheExistingFileADefaultOne(): bool
    {
        return ViewDiffService::checksumMatchesAny(unixsum_file(BladePage::path('index.blade.php')));
    }
}
