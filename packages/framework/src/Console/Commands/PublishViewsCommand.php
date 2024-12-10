<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Console\Concerns\Command;
use Hyde\Foundation\Providers\ViewServiceProvider;
use Hyde\Hyde;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

use function Hyde\path_join;
use function str_replace;
use function sprintf;
use function strstr;

/**
 * Publish the Hyde Blade views.
 */
class PublishViewsCommand extends Command
{
    /** @var string */
    protected $signature = 'publish:views {category? : The category to publish} {--i|interactive : Interactively select the views to publish}';

    /** @var string */
    protected $description = 'Publish the hyde components for customization. Note that existing files will be overwritten';

    /** @var array<string, array<string, string>> */
    protected array $options = [
        'layouts' => [
            'name' => 'Blade Layouts',
            'description' => 'Shared layout views, such as the app layout, navigation menu, and Markdown page templates',
            'group' => 'hyde-layouts',
        ],
        'components' => [
            'name' => 'Blade Components',
            'description' => 'More or less self contained components, extracted for customizability and DRY code',
            'group' => 'hyde-components',
        ],
        'page-404' => [
            'name' => '404 Page',
            'description' => 'A beautiful 404 error page by the Laravel Collective',
            'group' => 'hyde-page-404',
        ],
    ];

    public function handle(): int
    {
        $selected = (string) ($this->argument('category') ?? $this->promptForCategory());

        if ($selected === 'all' || $selected === '') {
            foreach ($this->options as $key => $_ignored) {
                $this->publishOption($key);
            }
        } else {
            if ($selected === 'components' && $this->isInteractive()) {
                $this->handleInteractivePublish();

                return Command::SUCCESS;
            }

            $this->publishOption($selected);
        }

        return Command::SUCCESS;
    }

    protected function isInteractive(): bool
    {
        return $this->option('interactive');
    }

    protected function publishOption(string $selected): void
    {
        Artisan::call('vendor:publish', [
            '--tag' => $this->options[$selected]['group'] ?? $selected,
            '--force' => true,
        ], $this->output);
    }

    protected function promptForCategory(): string
    {
        /** @var string $choice */
        $choice = $this->choice(
            'Which category do you want to publish?',
            $this->formatPublishableChoices(),
            0
        );

        $selection = $this->parseChoiceIntoKey($choice);

        $this->infoComment(sprintf("Selected category [%s]\n", $selection ?: 'all'));

        return $selection;
    }

    protected function formatPublishableChoices(): array
    {
        $keys = ['Publish all categories listed below'];
        foreach ($this->options as $key => $option) {
            $keys[] = "<comment>$key</comment>: {$option['description']}";
        }

        return $keys;
    }

    protected function parseChoiceIntoKey(string $choice): string
    {
        return strstr(str_replace(['<comment>', '</comment>'], '', $choice), ':', true) ?: '';
    }

    protected function handleInteractivePublish(): void
    {
        // Initially only works for components

        // Get all files in the components tag
        $paths = ServiceProvider::pathsToPublish(ViewServiceProvider::class, 'hyde-components');
        $source = key($paths);
        $target = $paths[$source];

        $sourceBaseDir = basename($source);
        $targetBaseDir = basename($target);

        // Now we need an array that maps all source files to their target paths retaining the directory structure
        $search = File::allFiles($source);

        $files = collect($search)->mapWithKeys(function (\Symfony\Component\Finder\SplFileInfo $file) use ($source, $target, $sourceBaseDir, $targetBaseDir) {
            $targetPath =  path_join($target, $file->getRelativePathname());

            return [Hyde::pathToRelative(realpath($file->getPathname())) => Hyde::pathToRelative($targetPath)];
        });
    }
}
