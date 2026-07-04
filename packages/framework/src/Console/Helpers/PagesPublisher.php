<?php

declare(strict_types=1);

namespace Hyde\Console\Helpers;

use Hyde\Console\Concerns\Command;
use Hyde\Enums\OverwriteAction;
use Hyde\Facades\Filesystem;
use Hyde\Framework\Services\OverwritePolicy;
use Hyde\Hyde;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;

use function array_map;
use function count;
use function Hyde\unixsum_file;
use function implode;
use function preg_replace;
use function sprintf;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

/**
 * The starter-page publishing flow for the {@see \Hyde\Console\Commands\PublishCommand}.
 *
 * Publishes pages from the {@see PublishablePages} registry into the project's _pages directory. Unlike views,
 * a page may have several valid destinations, so the flow is: select the pages, resolve each destination (§5.4:
 * --to → non-interactive default → interactive prompt → default), detect any two pages colliding on one target
 * (§5.6) before writing, confirm, then apply the shared {@see OverwritePolicy} exactly as the views flow does.
 *
 * @internal This helper is scoped to the publish command and should not be used elsewhere.
 */
class PagesPublisher
{
    /** Sentinel key for the "Custom path…" row in the destination prompt; real targets are _pages/ paths, so it never collides. */
    protected const CUSTOM = '__hyde_custom_target__';

    /** @var array<array{page: PublishablePage, target: string}> Pages skipped because the destination is already up to date. */
    protected array $current = [];

    /** @var array<array{page: PublishablePage, target: string}> Modified destinations left unchanged (interactive skip). */
    protected array $leftModified = [];

    /** Whether the pages were chosen through the interactive picker (which adds the §5.5 confirmation step). */
    protected bool $usedPicker = false;

    public function __construct(protected Command $command, protected InputInterface $input)
    {
    }

    public function publish(): int
    {
        // §5.4: --to names a single destination, so it is only meaningful for a single named page. A bare --page
        // (multi-select) with --to would have one path stand in for several pages; reject it rather than guess.
        if ($this->command->option('to') !== null && ! $this->hasNamedPage()) {
            $this->command->error('--to is only valid when publishing a single page. Use --page=NAME with --to.');

            return Command::FAILURE;
        }

        $pages = $this->selectPages();

        if ($pages === null) {
            return Command::FAILURE;
        }

        if ($pages === []) {
            $this->command->infoComment('No pages selected; nothing to publish.');

            return Command::SUCCESS;
        }

        $resolved = $this->resolveDestinations($pages);

        if ($resolved === null) {
            return Command::FAILURE;
        }

        if (! $this->assertNoDestinationConflicts($resolved)) {
            return Command::FAILURE;
        }

        if ($this->usedPicker && ! $this->confirmProceed($resolved)) {
            $this->command->infoComment('Cancelled. No pages were published.');

            return Command::SUCCESS;
        }

        $written = $this->write($resolved);

        // A null result means the run stopped after the decision but before any write: a non-interactive blocked
        // run without --force is a hard failure, while an interactive Cancel is a clean exit.
        if ($written === null) {
            return $this->canPrompt() ? Command::SUCCESS : Command::FAILURE;
        }

        $this->report($written);

        if ($written !== []) {
            $this->maybeRebuild();
        }

        return Command::SUCCESS;
    }

    /** @return array<PublishablePage>|null */
    protected function selectPages(): ?array
    {
        if ($this->hasNamedPage()) {
            $name = (string) $this->command->option('page');
            $page = $this->findPage($name);

            if ($page === null) {
                $this->command->error("The page [$name] does not exist.");
                $this->command->line('Available pages: '.implode(', ', array_map(fn (PublishablePage $page): string => $page->key, PublishablePages::all())));

                return null;
            }

            return [$page];
        }

        if (! $this->canPrompt()) {
            $this->command->error('No page specified for publishing. Provide one, for example --page=welcome.');

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
        // 1. An explicit --to wins, but only for pages that allow a custom destination (e.g. not 404), and it is
        //    validated against _pages/ and the .blade.php extension.
        if ($this->command->option('to') !== null) {
            if (! $page->allowCustomTarget) {
                $this->command->error(sprintf('The [%s] page cannot be published to a custom path; omit --to to use its default (%s).', $page->key, $page->defaultTarget));

                return null;
            }

            return $this->validateCustomTarget((string) $this->command->option('to'));
        }

        // 2. Non-interactive falls back to the default; a page without one (e.g. blank) cannot be resolved.
        if (! $this->canPrompt()) {
            if ($page->defaultTarget === null) {
                $this->command->error(sprintf('The [%s] page has no default destination. Provide one with --to.', $page->key));

                return null;
            }

            return $page->defaultTarget;
        }

        // 3. Interactively, prompt only when the destination is genuinely ambiguous: the page offers alternative
        //    targets, or it has no default at all. A page whose default is the one sensible destination (welcome, 404)
        //    is not prompted for — its custom placement, if allowed, is reached through --to instead. This keeps the
        //    common "publish the welcome homepage" case a single, frictionless step.
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

    protected function validateCustomTarget(string $path): ?string
    {
        $normalized = $this->normalizeTargetPath($path);

        if (! $this->isValidCustomTarget($normalized)) {
            $this->command->error('The --to path must be within _pages/ and end in .blade.php, for example _pages/index.blade.php.');

            return null;
        }

        return $normalized;
    }

    protected function isValidCustomTarget(string $path): bool
    {
        $normalized = $this->normalizeTargetPath($path);

        return Str::startsWith($normalized, '_pages/')
            && ! Str::contains($normalized, '..')
            && Str::endsWith($normalized, '.blade.php');
    }

    protected function normalizeTargetPath(string $path): string
    {
        return (string) preg_replace('#/+#', '/', Str::replace('\\', '/', $path));
    }

    /**
     * Reject the run when two selected pages resolve to the same destination (§5.6), before anything is written.
     *
     * @param  array<array{page: PublishablePage, target: string}>  $resolved
     */
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
                $this->command->error(sprintf('%s %s %s.', $this->joinLabels($labels), $verb, $target));
                $this->command->line('Pick one, or set --to for each.');

                return false;
            }
        }

        return true;
    }

    /** @param  array<array{page: PublishablePage, target: string}>  $resolved */
    protected function confirmProceed(array $resolved): bool
    {
        $this->command->line('Ready to publish:');

        foreach ($resolved as $entry) {
            $this->command->line(sprintf('  %s → %s', $entry['page']->label, $entry['target']));
        }

        $this->command->newLine();

        return confirm('Proceed?', true);
    }

    /**
     * @param  array<array{page: PublishablePage, target: string}>  $resolved
     * @return array<array{page: PublishablePage, target: string, source: string, absolute: string, destinationChecksum?: string}>|null
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
                $this->current[] = $entry;
            } else {
                $record['destinationChecksum'] = $this->destinationChecksum($record['absolute']);
                $blocked[] = $record;
            }
        }

        $overwrite = $this->resolveBlocked($blocked);

        if ($overwrite === null) {
            return null;
        }

        $this->leftModified = $overwrite === [] ? array_map(fn (array $record): array => ['page' => $record['page'], 'target' => $record['target']], $blocked) : [];

        $written = $this->refreshApprovedWrites($copy, $overwrite);

        foreach ($written as $record) {
            $this->copy($record['source'], $record['absolute']);
        }

        return $written;
    }

    /**
     * Re-check destinations immediately before copying so a file changed during an interactive prompt is not lost.
     *
     * @param  array<array{page: PublishablePage, target: string, source: string, absolute: string, destinationChecksum?: string}>  $copy
     * @param  array<array{page: PublishablePage, target: string, source: string, absolute: string, destinationChecksum?: string}>  $overwrite
     * @return array<array{page: PublishablePage, target: string, source: string, absolute: string, destinationChecksum?: string}>
     */
    protected function refreshApprovedWrites(array $copy, array $overwrite): array
    {
        $written = [];

        foreach ($copy as $record) {
            $this->refreshApprovedWrite($written, $record, false);
        }

        foreach ($overwrite as $record) {
            $this->refreshApprovedWrite($written, $record, true);
        }

        return $written;
    }

    /**
     * @param  array<array{page: PublishablePage, target: string, source: string, absolute: string, destinationChecksum?: string}>  $written
     * @param  array{page: PublishablePage, target: string, source: string, absolute: string, destinationChecksum?: string}  $record
     */
    protected function refreshApprovedWrite(array &$written, array $record, bool $approvedOverwrite): void
    {
        match (OverwritePolicy::decide($record['source'], $record['absolute'])) {
            OverwriteAction::Copy => $written[] = $record,
            OverwriteAction::Skip => $this->current[] = ['page' => $record['page'], 'target' => $record['target']],
            OverwriteAction::Blocked => $this->handleStillBlockedWrite($written, $record, $approvedOverwrite),
        };
    }

    /**
     * @param  array<array{page: PublishablePage, target: string, source: string, absolute: string, destinationChecksum?: string}>  $written
     * @param  array{page: PublishablePage, target: string, source: string, absolute: string, destinationChecksum?: string}  $record
     */
    protected function handleStillBlockedWrite(array &$written, array $record, bool $approvedOverwrite): void
    {
        if (! $approvedOverwrite || ($record['destinationChecksum'] ?? null) !== $this->destinationChecksum($record['absolute'])) {
            throw new RuntimeException("Cannot publish: destination [{$record['target']}] changed after overwrite checks. Run the command again.");
        }

        $written[] = $record;
    }

    protected function copy(string $source, string $target): void
    {
        Filesystem::ensureParentDirectoryExists($target);

        if (! Filesystem::copy($source, $target)) {
            throw new RuntimeException("Failed to copy [$source] to [$target].");
        }
    }

    /**
     * Resolve what to do with modified (blocked) destinations, mirroring the views flow (§7).
     *
     * @param  array<array{page: PublishablePage, target: string, source: string, absolute: string, destinationChecksum?: string}>  $blocked
     * @return array<array{page: PublishablePage, target: string, source: string, absolute: string, destinationChecksum?: string}>|null
     */
    protected function resolveBlocked(array $blocked): ?array
    {
        if ($blocked === []) {
            return [];
        }

        if ($this->command->option('force')) {
            return $blocked;
        }

        if (! $this->canPrompt()) {
            $this->command->error('Cannot overwrite modified files without --force:');

            foreach ($blocked as $record) {
                $this->command->line('  '.$record['target']);
            }

            $this->command->newLine();
            $this->command->line('Run again with --force to overwrite.');

            return null;
        }

        $choice = select(sprintf('%d selected files already exist and appear modified.', count($blocked)), [
            'skip' => 'Skip modified files',
            'overwrite' => 'Overwrite modified files',
            'cancel' => 'Cancel',
        ], 'skip');

        if ($choice === 'overwrite') {
            return $blocked;
        }

        if ($choice === 'skip') {
            return [];
        }

        // Cancelling the overwrite prompt aborts the whole run; announce it as the views flow and the
        // "Proceed? no" path both do, so the exit is never silent. This branch is only reached interactively.
        $this->command->infoComment('Cancelled. No pages were published.');

        return null;
    }

    protected function destinationChecksum(string $target): string
    {
        return unixsum_file($target);
    }

    /** @param  array<array{page: PublishablePage, target: string, source: string, absolute: string, destinationChecksum?: string}>  $written */
    protected function report(array $written): void
    {
        if ($written === [] && $this->leftModified === [] && $this->current !== []) {
            $this->command->infoComment('All selected pages are already up to date.');

            return;
        }

        foreach ($written as $record) {
            $this->command->infoComment(sprintf('Published [%s] to [%s]', $record['page']->key, $record['target']));
        }

        if ($this->current !== []) {
            $this->command->infoComment(sprintf('%s already up to date and skipped.', $this->pageCount(count($this->current))));
        }

        if ($this->leftModified !== []) {
            $this->command->newLine();
            $this->command->warn(sprintf('%s left unchanged because they were modified:', $this->pageCount(count($this->leftModified))));

            foreach ($this->leftModified as $entry) {
                $this->command->line('  '.$entry['target']);
            }

            $this->command->line('Run again with --force to overwrite.');
        }
    }

    /**
     * Offer to rebuild the site after a successful publish (§5.7).
     *
     * Interactive only, and deliberately defaulting to NO. A single page publish should not auto-rebuild the
     * entire site, so the prompt defaults to NO — keep this check here rather than consolidating it into a
     * shared rebuild helper, which would tempt a yes-default back in.
     */
    protected function maybeRebuild(): void
    {
        if (! $this->canPrompt()) {
            return;
        }

        if (confirm('Rebuild the site now?', false)) {
            Artisan::call('build', [], $this->command->getOutput());
        }
    }

    protected function hasNamedPage(): bool
    {
        $name = $this->command->option('page');

        return $name !== null && $name !== '';
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

    protected function canPrompt(): bool
    {
        return ConsoleHelper::canUseLaravelPrompts($this->input);
    }
}
