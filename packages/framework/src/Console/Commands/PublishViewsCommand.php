<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Framework\Actions\PublishesHydeViews;
use Hyde\Hyde;
use LaravelZero\Framework\Commands\Command;

/**
 * Publish the Hyde Blade views.
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
        (new PublishesHydeViews($selected))->execute();

        $from = Hyde::vendorPath(PublishesHydeViews::$options[$selected]['path']);
        $from = substr($from, strpos($from, 'vendor'));

        $to = (PublishesHydeViews::$options[$selected]['destination']);

        $this->line("<info>Copied</info> [<comment>$from</comment>] <info>to</info> [<comment>$to</comment>]");
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
