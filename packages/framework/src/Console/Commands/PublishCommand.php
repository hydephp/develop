<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Console\Concerns\Command;
use Hyde\Console\Helpers\PagesPublisher;
use Hyde\Console\Helpers\ViewsPublisher;
use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function is_string;
use function Laravel\Prompts\select;

/**
 * The flag-driven, views-centric publishing command for Hyde Blade customizations,
 * with an optional side path for publishing starter pages.
 *
 * This is the command spine: it owns the full flag surface, all guardrails, and the
 * interactive wizard routing. The actual views and pages publishing are delegated to
 * handlers that are stubbed out in this step and filled in by later steps.
 */
class PublishCommand extends Command
{
    /** @var string */
    protected $signature = 'publish
        {--layouts : Scope publishing to the Hyde layout views}
        {--components : Scope publishing to the Hyde component views}
        {--all : Publish all Hyde views without the picker}
        {--page= : Publish a starter page, optionally by name (e.g. --page=welcome)}
        {--to= : Destination path for a published page (pages only)}
        {--force : Overwrite files that you have modified}';

    /** @var string */
    protected $description = 'Publish Hyde views and starter pages for customization';

    /**
     * Intercept the raw input before Symfony's strict option binding so we can redirect the
     * curated-out tag/provider/config publishing flags to vendor:publish with a helpful message.
     *
     * We deliberately do not declare these as options: doing so would advertise them in the
     * command's help output, which is exactly the raw-publishing surface this command exists to
     * hide. By only short-circuiting these three specific tokens, a genuine typo such as --layout
     * still falls through to Symfony's native "unknown option" error.
     */
    public function run(InputInterface $input, OutputInterface $output): int
    {
        if ($input->hasParameterOption(['--tag', '--provider', '--config'], true)) {
            $this->output = $output instanceof OutputStyle ? $output : $this->laravel->make(
                OutputStyle::class, ['input' => $input, 'output' => $output]
            );

            return $this->redirectRawPublishingFlags($input);
        }

        return parent::run($input, $output);
    }

    protected function safeHandle(): int
    {
        if ($this->option('layouts') && $this->option('components')) {
            $this->error('The --layouts and --components options are mutually exclusive. Use --all to publish both.');

            return Command::FAILURE;
        }

        if ($this->hasEmptyPageOption()) {
            $this->error('The --page option cannot be empty. Use --page for the picker or --page=welcome.');

            return Command::FAILURE;
        }

        if ($this->option('to') !== null && ! $this->wantsToPublishPage()) {
            $this->error('--to is only valid when publishing a page.');

            return Command::FAILURE;
        }

        if ($this->wantsToPublishPage()) {
            return $this->publishPage();
        }

        if ($this->wantsToPublishViews()) {
            return $this->publishViews();
        }

        // No actionable flags were supplied. We must decide before attempting any prompt: without
        // an interactive terminal there is no wizard to run, so we fail with usage guidance instead.
        if (! $this->input->isInteractive()) {
            return $this->failWithUsageHint();
        }

        return $this->runWizard();
    }

    /** Interactive step 1 (§3): route to the views or pages flow, or cancel out. */
    protected function runWizard(): int
    {
        $choice = select('What do you want to publish?', [
            'views' => 'Views — customize Hyde Blade layouts and components',
            'page' => 'A starter page — copy a homepage, 404, or other default page',
            'cancel' => 'Cancel',
        ], 'views');

        return match ($choice) {
            'views' => $this->publishViews(),
            'page' => $this->publishPage(),
            default => Command::SUCCESS, // Cancelling is a clean exit, not an error.
        };
    }

    protected function publishViews(): int
    {
        return (new ViewsPublisher($this, $this->input))->publish();
    }

    protected function publishPage(): int
    {
        return (new PagesPublisher($this, $this->input))->publish();
    }

    protected function wantsToPublishViews(): bool
    {
        return $this->option('layouts') || $this->option('components') || $this->option('all');
    }

    /**
     * The --page flag is value-optional, so a bare --page and an absent --page both read as null
     * via option(). We check the raw input for its presence to tell the two apart.
     */
    protected function wantsToPublishPage(): bool
    {
        return $this->input->hasParameterOption(['--page', '--page=']) || $this->option('page') !== null;
    }

    protected function hasEmptyPageOption(): bool
    {
        return $this->input->hasParameterOption('--page=')
            || ($this->input->hasParameterOption('--page') && $this->input->getParameterOption('--page', false) === '');
    }

    protected function failWithUsageHint(): int
    {
        $this->error('Nothing to publish. Try:');
        $this->line('  php hyde publish --all');
        $this->line('  php hyde publish --layouts');
        $this->line('  php hyde publish --page=welcome');

        return Command::FAILURE;
    }

    protected function redirectRawPublishingFlags(InputInterface $input): int
    {
        if ($input->hasParameterOption('--config', true)) {
            $this->error('Config is not published through this command. Use php hyde vendor:publish --tag=hyde-config instead.');

            return Command::FAILURE;
        }

        $flag = $input->hasParameterOption('--tag', true) ? 'tag' : 'provider';
        $value = $input->getParameterOption("--$flag", null, true);
        $hint = is_string($value) && $value !== '' ? "--$flag=$value" : "--$flag";

        $this->error("Use php hyde vendor:publish $hint for tag/provider publishing.");

        return Command::FAILURE;
    }
}
