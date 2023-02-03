<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Console\Concerns\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * Publish the Hyde Blade views.
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\PublishViewsCommandTest
 */
class PublishViewsCommand extends Command
{
    /** @var string */
    protected $signature = 'publish:views {category? : The category to publish}';

    /** @var string */
    protected $description = 'Publish the hyde components for customization. Note that existing files will be overwritten.';

    protected string $selected;

    protected array $options = [
        'layouts' => [
            'name' => 'Blade Layouts',
            'description' => 'Shared layout views, such as the app layout, navigation menu, and Markdown page templates.',
            'group' => 'hyde-layouts',
        ],
        'components' => [
            'name' => 'Blade Components',
            'description' => 'More or less self contained components, extracted for customizability and DRY code.',
            'group' => 'hyde-components',
        ],
        'page-404' => [
            'name' => '404 Page',
            'description' => 'A beautiful 404 error page by the Laravel Collective.',
            'group' => 'hyde-page-404',
        ],
    ];

    public function handle(): int
    {
        $this->selected = $this->argument('category') ?? $this->promptForCategory();

        if ($this->selected === 'all' || $this->selected === '') {
            foreach ($this->options as $key => $value) {
                $this->publishOption($key);
            }
        } else {
            $this->publishOption($this->selected);
        }

        return Command::SUCCESS;
    }

    protected function publishOption(string $selected): void
    {
        Artisan::call('vendor:publish', [
            '--tag' => $this->options[$selected]['group'],
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

        $choice = $this->parseChoiceIntoKey($choice);

        $this->infoComment(sprintf(
            "Selected category [%s]\n",
            empty($choice) ? 'all' : $choice
        ));

        return $choice;
    }

    protected function formatPublishableChoices(): array
    {
        $keys = [];
        $keys[] = 'Publish all categories listed below';
        foreach ($this->options as $key => $value) {
            $keys[] = "<comment>$key</comment>: {$value['description']}";
        }

        return $keys;
    }

    protected function parseChoiceIntoKey(string $choice): string
    {
        return strstr(str_replace(['<comment>', '</comment>'], '', $choice), ':', true) ?: '';
    }
}
