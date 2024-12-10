<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Console\Concerns\Command;
use Hyde\Console\Helpers\InteractivePublishCommandHelper;
use Hyde\Hyde;
use Illuminate\Support\Facades\Artisan;

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
        // Todo: Don't trigger interactive if "all" is selected
        if ($this->isInteractive()) {
            $this->handleInteractivePublish($selected);

            return;
        }

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

    protected function handleInteractivePublish(string $selected): void
    {
        $group = $this->options[$selected]['group'];
        $publisher = new InteractivePublishCommandHelper($group);

        $message = $publisher->handle();
        $this->infoComment($message);
    }
}
