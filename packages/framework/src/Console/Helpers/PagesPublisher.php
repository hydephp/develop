<?php

declare(strict_types=1);

namespace Hyde\Console\Helpers;

use Hyde\Console\Concerns\Command;
use Hyde\Enums\OverwriteAction;
use Hyde\Framework\Services\OverwritePolicy;
use Hyde\Hyde;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use League\Flysystem\FilesystemException;
use League\Flysystem\WhitespacePathNormalizer;

use function array_merge;
use function array_map;
use function count;
use function implode;
use function is_string;
use function sprintf;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

/**
 * @internal This helper is scoped to the publish command and should not be used elsewhere.
 */
class PagesPublisher extends BasePublisher
{
    /** Sentinel key for the "Custom path…" row in the destination prompt; real targets are _pages/ paths, so it never collides. */
    protected const CUSTOM = '__hyde_custom_target__';

    /** @var array<array{page: PublishablePage, target: string}> Pages skipped because the destination is already up to date. */
    protected array $current = [];

    /** @var array<array{page: PublishablePage, target: string}> Modified destinations left unchanged (interactive skip). */
    protected array $leftModified = [];

    /** Whether the pages were chosen through the interactive picker (which adds a confirmation step). */
    protected bool $usedPicker = false;

    public function __construct(protected PublisherConsole $console)
    {
    }

    public function publish(): int
    {
        // --to names a single destination, so it is only meaningful for a single named page. A bare --page
        // (multi-select) with --to would have one path stand in for several pages; reject it rather than guess.
        if ($this->console->option('to') !== null && ! $this->hasNamedPage()) {
            $this->console->error('--to is only valid when publishing a single page. Use --page=NAME with --to.');

            return Command::FAILURE;
        }

        $pages = $this->selectPages();

        if ($pages === null) {
            return Command::FAILURE; // A guidance message was already printed.
        }

        if ($pages === []) {
            $this->console->infoComment('No pages selected; nothing to publish.');

            return Command::SUCCESS;
        }

        $resolved = $this->resolveDestinations($pages);

        if ($resolved === null) {
            // A destination could not be resolved (invalid --to, an invalid custom path, or a page with no
            // default in non-interactive mode). A guidance message was already printed; this is always a failure.
            return Command::FAILURE;
        }

        if (! $this->assertNoDestinationConflicts($resolved)) {
            return Command::FAILURE;
        }

        if ($this->usedPicker && ! $this->confirmProceed($resolved)) {
            $this->console->infoComment('Cancelled. No pages were published.');

            return Command::SUCCESS;
        }

        $written = $this->write($resolved);

        // A null result means the run stopped after the decision but before any write: a non-interactive blocked
        // run without --force is a hard failure, while an interactive Cancel is a clean exit.
        if ($written === null) {
            return $this->console->canPrompt() ? Command::SUCCESS : Command::FAILURE;
        }

        $this->report($written);

        if ($written !== []) {
            $this->maybeRebuild();
        }

        return $this->hasPolicyErrors() ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * Determine which pages to publish: a named page directly, or the interactive picker.
     *
     * @return array<PublishablePage>|null The selected pages, or null when the run should fail (message printed).
     */
    protected function selectPages(): ?array
    {
        if ($this->hasNamedPage()) {
            $name = (string) $this->console->option('page');
            $page = $this->findPage($name);

            if ($page === null) {
                $this->console->error("The page [$name] does not exist.");
                $this->console->line('Available pages: '.implode(', ', array_map(fn (PublishablePage $page): string => $page->key, PublishablePages::all())));

                return null;
            }

            return [$page];
        }

        if (! $this->console->canPrompt()) {
            $this->console->error('No page specified for publishing. Provide one, for example --page=welcome.');

            return null;
        }

        $this->usedPicker = true;

        return $this->promptForPages();
    }

    /** @return array<PublishablePage> */
    protected function promptForPages(): array
    {
        $options = [];

        foreach (PublishablePages::all() as $page) {
            $options[$page->key] = $this->pickerLabel($page);
        }

        $selected = InteractiveMultiselect::select('Select pages to publish', $options);

        // Cast defends against PHP coercing a numeric key such as '404' to an int on the way back through the prompt.
        return array_map(fn ($key): PublishablePage => $this->findPage((string) $key), $selected);
    }

    protected function pickerLabel(PublishablePage $page): string
    {
        return $page->defaultTarget !== null
            ? sprintf('%s → %s', $page->label, $page->defaultTarget)
            : $page->label;
    }

    /**
     * Resolve the destination for each selected page, in registry order.
     *
     * @param  array<PublishablePage>  $pages
     * @return array<array{page: PublishablePage, target: string}>|null Null when resolution failed or was cancelled.
     */
    protected function resolveDestinations(array $pages): ?array
    {
        $resolved = [];

        foreach ($pages as $page) {
            $target = $this->resolveTarget($page);

            if ($target === null) {
                return null;
            }

            $resolved[] = ['page' => $page, 'target' => $this->normalizeTargetPath($target)];
        }

        return $resolved;
    }

    protected function resolveTarget(PublishablePage $page): ?string
    {
        if ($this->console->option('to') !== null) {
            if (! $page->allowCustomTarget) {
                $this->console->error(sprintf('The [%s] page cannot be published to a custom path; omit --to to use its default (%s).', $page->key, $page->defaultTarget));

                return null;
            }

            return $this->validateCustomTarget((string) $this->console->option('to'));
        }

        if (! $this->console->canPrompt()) {
            if ($page->defaultTarget === null) {
                $this->console->error(sprintf('The [%s] page has no default destination. Provide one with --to.', $page->key));

                return null;
            }

            return $page->defaultTarget;
        }

        // Only prompt when the destination is genuinely ambiguous: alternative targets exist, or there is no
        // default at all. A page with one sensible default (welcome, 404) is never prompted for.
        if ($page->alternativeTargets !== [] || $page->defaultTarget === null) {
            return $this->promptForTarget($page);
        }

        return $page->defaultTarget;
    }

    protected function promptForTarget(PublishablePage $page): ?string
    {
        $options = [];

        if ($page->defaultTarget !== null) {
            $options[$page->defaultTarget] = sprintf('%s (default)', $page->defaultTarget);
        }

        foreach ($page->alternativeTargets as $path => $label) {
            $options[$path] = sprintf('%s (%s)', $path, $label);
        }

        if ($page->allowCustomTarget) {
            $options[self::CUSTOM] = 'Custom path…';
        }

        $choice = (string) select(sprintf('Where should "%s" be published?', $page->label), $options);

        return $choice === self::CUSTOM ? $this->promptForCustomTarget() : $choice;
    }

    protected function promptForCustomTarget(): ?string
    {
        $path = text(
            label: 'Enter a path within _pages/',
            placeholder: '_pages/example.blade.php',
            required: true,
            validate: fn (string $value): ?string => $this->isValidCustomTarget($value)
                ? null
                : 'The path must be within _pages/ and end in .blade.php.'
        );

        return $this->normalizeTargetPath($path);
    }

    /** Validate a user-supplied destination: it must live under _pages/ and be a Blade page. Returns null on failure. */
    protected function validateCustomTarget(string $path): ?string
    {
        if (! $this->isValidCustomTarget($path)) {
            $this->console->error('The --to path must be within _pages/ and end in .blade.php, for example _pages/index.blade.php.');

            return null;
        }

        return $this->normalizeTargetPath($path);
    }

    protected function isValidCustomTarget(string $path): bool
    {
        try {
            $normalized = $this->normalizeTargetPath($path);
        } catch (FilesystemException) {
            return false;
        }

        return Str::startsWith($normalized, '_pages/')
            && Str::endsWith($normalized, '.blade.php');
    }

    protected function normalizeTargetPath(string $path): string
    {
        return (new WhitespacePathNormalizer())->normalizePath($path);
    }

    /** @param  array<array{page: PublishablePage, target: string}>  $resolved */
    protected function assertNoDestinationConflicts(array $resolved): bool
    {
        $labelsByTarget = [];

        foreach ($resolved as $entry) {
            $labelsByTarget[$entry['target']][] = $entry['page']->label;
        }

        foreach ($labelsByTarget as $target => $labels) {
            if (count($labels) > 1) {
                // "both" reads correctly for a pair; three or more colliding pages need "all".
                $verb = count($labels) === 2 ? 'both target' : 'all target';
                $this->console->error(sprintf('%s %s %s.', $this->joinLabels($labels), $verb, $target));
                $this->console->line('Pick one, or set --to for each.');

                return false;
            }
        }

        return true;
    }

    /** @param  array<array{page: PublishablePage, target: string}>  $resolved */
    protected function confirmProceed(array $resolved): bool
    {
        $this->console->line('Ready to publish:');

        foreach ($resolved as $entry) {
            $this->console->line(sprintf('  %s → %s', $entry['page']->label, $entry['target']));
        }

        $this->console->newLine();

        return confirm('Proceed?', true);
    }

    /**
     * Apply the shared overwrite policy and copy the resolved pages into place.
     *
     * @param  array<array{page: PublishablePage, target: string}>  $resolved
     * @return array<array{page: PublishablePage, target: string, source: string, absolute: string}>|null The pages actually written, or null when the run should stop (cancelled, or blocked without --force).
     */
    protected function write(array $resolved): ?array
    {
        $copy = [];
        $blocked = [];

        foreach ($resolved as $entry) {
            $record = [
                'page' => $entry['page'],
                'target' => $entry['target'],
                'source' => Hyde::vendorPath($entry['page']->source),
                'absolute' => Hyde::path($entry['target']),
            ];

            $action = OverwritePolicy::decide($record['source'], $record['absolute']);

            if ($action === OverwriteAction::Copy) {
                $copy[] = $record;
            } elseif ($action === OverwriteAction::Skip) {
                $this->noteCurrent($record);
            } elseif ($action === OverwriteAction::Error) {
                $this->reportPolicyError($this->console, $record['source'], $record['absolute']);
            } else {
                $blocked[] = $record;
            }
        }

        $overwrite = $this->console->resolveBlocked($blocked, 'Cancelled. No pages were published.');

        if ($overwrite === null) {
            return null;
        }

        $this->leftModified = $overwrite === [] ? array_map(fn (array $record): array => ['page' => $record['page'], 'target' => $record['target']], $blocked) : [];

        $written = array_merge($copy, $overwrite);

        foreach ($written as $record) {
            $this->copy($record['source'], $record['absolute']);
        }

        return $written;
    }

    /**
     * @param  array{page: PublishablePage, target: string, source: string, absolute: string}  $record
     */
    protected function noteCurrent(array $record): void
    {
        $this->current[] = ['page' => $record['page'], 'target' => $record['target']];
    }

    /** @param  array<array{page: PublishablePage, target: string, source: string, absolute: string}>  $written */
    protected function report(array $written): void
    {
        if ($written === [] && $this->leftModified === [] && $this->current !== []) {
            $this->console->infoComment('All selected pages are already up to date.');

            return;
        }

        foreach ($written as $record) {
            $this->console->infoComment(sprintf('Published [%s] to [%s]', $record['page']->key, $record['target']));
        }

        if ($this->current !== []) {
            $this->console->infoComment(sprintf('%s already up to date and skipped.', $this->pageCount(count($this->current))));
        }

        if ($this->leftModified !== []) {
            $this->console->newLine();
            $this->console->warn(sprintf('%s left unchanged because they were modified:', $this->pageCount(count($this->leftModified))));

            foreach ($this->leftModified as $entry) {
                $this->console->line('  '.$entry['target']);
            }

            $this->console->line('Run again with --force to overwrite.');
        }
    }

    /**
     * Interactive only, and deliberately defaulting to NO: a single page publish should not auto-rebuild
     * the entire site. Keep this check here rather than moving it into a shared rebuild helper, which
     * would tempt a yes-default back in.
     */
    protected function maybeRebuild(): void
    {
        if (! $this->console->canPrompt()) {
            return;
        }

        if (confirm('Rebuild the site now?', false)) {
            Artisan::call('build', [], $this->console->getOutput());
        }
    }

    /** Whether a specific page name was supplied via --page=NAME (as opposed to a bare --page or the wizard). */
    protected function hasNamedPage(): bool
    {
        $name = $this->console->option('page');

        return is_string($name) && $name !== '';
    }

    /** Find a page by key, comparing the ->key property so a numeric key such as '404' is never lost to array coercion. */
    protected function findPage(string $name): ?PublishablePage
    {
        foreach (PublishablePages::all() as $page) {
            if ($page->key === $name) {
                return $page;
            }
        }

        return null;
    }

    /** @param  array<string>  $labels */
    protected function joinLabels(array $labels): string
    {
        return Arr::join($labels, ', ', ' and ');
    }

    protected function pageCount(int $count): string
    {
        return $count === 1 ? '1 page' : "$count pages";
    }
}
