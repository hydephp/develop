<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Hyde;
use Illuminate\Support\Facades\Artisan;
use LaravelZero\Framework\Commands\Command;

/**
 * Publish the Hyde Blade views.
 *
 * @deprecated May be replaced by vendor:publish in the future.
 */
class PublishViewsCommand extends Command
{
    /** @var string */
    protected $signature = 'publish:views {category? : The category to publish}';

    /** @var string */
    protected $description = 'Publish the hyde components for customization. Note that existing files will be overwritten.';

    protected string $selected;

    public static array $options = [
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
        '404' => [
            'name' => '404 Page',
            'description' => 'A beautiful 404 error page by the Laravel Collective.',
            'group' => 'hyde-page-404',
        ],
    ];

    public function handle(): int
    {
        $this->selected = $this->argument('category') ?? $this->promptForCategory();

        if ($this->selected === 'all' || $this->selected === '') {
            foreach (static::$options as $key => $value) {
                $this->publishOption((string) $key);
            }
        } else {
            $this->publishOption($this->selected);
        }

        return Command::SUCCESS;
    }

    protected function publishOption(string $selected): void
    {
        Artisan::call('vendor:publish', [
            '--tag' => static::$options[$selected]['group'],
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

        $this->line(sprintf(
            "<info>Selected category</info> [<comment>%s</comment>]\n",
            empty($choice) ? 'all' : $choice
        ));

        return $choice;
    }

    protected function formatPublishableChoices(): array
    {
        $keys = [];
        $keys[] = 'Publish all categories listed below';
        foreach (static::$options as $key => $value) {
            $keys[] = "<comment>$key</comment>: {$value['description']}";
        }

        return $keys;
    }

    protected function parseChoiceIntoKey(string $choice): string
    {
        return strstr(str_replace(['<comment>', '</comment>'], '', $choice), ':', true) ?: '';
    }
}
