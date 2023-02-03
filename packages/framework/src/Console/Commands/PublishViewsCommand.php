<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Framework\Actions\PublishesHydeViews;
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

    public function handle(): int
    {
        $this->selected = $this->argument('category') ?? $this->promptForCategory();

        if ($this->selected === 'all' || $this->selected === '') {
            foreach (PublishesHydeViews::$options as $key => $value) {
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
            '--tag' => PublishesHydeViews::$options[$selected]['group'],
            '--force' => true,
        ]);
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
        foreach (PublishesHydeViews::$options as $key => $value) {
            $keys[] = "<comment>$key</comment>: {$value['description']}";
        }

        return $keys;
    }

    protected function parseChoiceIntoKey(string $choice): string
    {
        return strstr(str_replace(['<comment>', '</comment>'], '', $choice), ':', true) ?: '';
    }
}
