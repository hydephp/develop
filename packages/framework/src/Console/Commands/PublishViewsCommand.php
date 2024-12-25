<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Console\Concerns\Command;
use Hyde\Console\Helpers\InteractivePublishCommandHelper;
use Hyde\Console\Helpers\ViewPublishGroup;
use Illuminate\Support\Str;
use Laravel\Prompts\MultiSelectPrompt;
use Laravel\Prompts\SelectPrompt;

use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\select;
use function str_replace;
use function sprintf;
use function strstr;

/**
 * Publish the Hyde Blade views.
 */
class PublishViewsCommand extends Command
{
    /** @var string */
    protected $signature = 'publish:views {category? : The category to publish}';

    /** @var string */
    protected $description = 'Publish the Hyde components for customization. Note that existing files will be overwritten';

    /** @var array<string, \Hyde\Console\Helpers\ViewPublishGroup> */
    protected array $options;

    public function handle(): int
    {
        $this->options = static::mapToKeys([
            ViewPublishGroup::fromGroup('hyde-layouts', 'Blade Layouts', 'Shared layout views, such as the app layout, navigation menu, and Markdown page templates'),
            ViewPublishGroup::fromGroup('hyde-components', 'Blade Components', 'More or less self contained components, extracted for customizability and DRY code'),
            ViewPublishGroup::fromGroup('hyde-page-404', '404 Page', 'A beautiful 404 error page by the Laravel Collective'),
        ]);

        $selected = (string) ($this->argument('category') ?? $this->promptForCategory());

        if (! in_array($selected, $allowed = array_merge(['all'], array_keys($this->options)), true)) {
            $this->error("Invalid selection: '$selected'");
            $this->infoComment('Allowed values are: ['.implode(', ', $allowed).']');

            return Command::FAILURE;
        }

        if ($selected === 'all' || $selected === '') {
            foreach ($this->options as $key => $_ignored) {
                $this->publishOption($key, true);
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

    protected function publishOption(string $selected, bool $isPublishingAll = false): void
    {
        $publisher = new InteractivePublishCommandHelper($this->options[$selected]['group']);

        $choices = $publisher->getFileChoices();

        MultiSelectPrompt::fallbackUsing(fn (): array => $choices);

        $selectedFiles = multiselect('Select the files you want to publish (CTRL+A to toggle all)', $choices, [], 10, 'required', hint: 'Navigate with arrow keys, space to select, enter to confirm.');

        $publisher->handle($selectedFiles);

        $this->infoComment($publisher->formatOutput($selectedFiles));
    }

    protected function promptForCategory(): string
    {
        SelectPrompt::fallbackUsing(function (SelectPrompt $prompt): string {
            return $this->choice($prompt->label, $prompt->options, $prompt->default);
        });

        $choice = select('Which category do you want to publish?', $this->formatPublishableChoices(), 0);

        $selection = $this->parseChoiceIntoKey($choice);

        $this->infoComment(sprintf("Selected category [%s]\n", $selection ?: 'all'));

        return $selection;
    }

    protected function formatPublishableChoices(): array
    {
        return collect($this->options)
            ->map(fn (ViewPublishGroup $option, string $key): string => sprintf("<comment>%s</comment>: %s", $key, $option->description))
            ->prepend('Publish all categories listed below')
            ->values()
            ->all();
    }

    protected function parseChoiceIntoKey(string $choice): string
    {
        return strstr(str_replace(['<comment>', '</comment>'], '', $choice), ':', true) ?: '';
    }

    /**
     * @param array<string, ViewPublishGroup> $groups
     * @return array<string, ViewPublishGroup>
     */
    protected static function mapToKeys(array $groups): array
    {
        return collect($groups)->mapWithKeys(function (ViewPublishGroup $group): array {
            return [Str::after($group->group, 'hyde-') => $group];
        })->all();
    }
}
