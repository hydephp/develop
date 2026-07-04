<?php

declare(strict_types=1);

namespace Hyde\Console\Helpers;

use Hyde\Console\Concerns\Command;
use Hyde\Enums\OverwriteAction;
use Hyde\Facades\Filesystem;
use Hyde\Framework\Services\OverwritePolicy;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputInterface;

use function array_intersect;
use function array_keys;
use function array_merge;
use function count;
use function explode;
use function implode;
use function reset;
use function sprintf;

use function Laravel\Prompts\select;

/**
 * The views publishing flow for the {@see \Hyde\Console\Commands\PublishCommand}.
 *
 * Publishes Hyde's Blade overrides from the two declared groups (layouts, components) into
 * resources/views/vendor/hyde/. The flow is: decide every selected file's outcome first (via the shared
 * {@see OverwritePolicy}), resolve any modified-file conflicts second (interactive prompt or --force), and
 * only then write — so cancelling never leaves a half-published tree. Output is cardinality-aware and
 * reports the full breakdown of what was copied, what was already current, and what was left modified.
 *
 * @internal This helper is scoped to the publish command and should not be used elsewhere.
 */
class ViewsPublisher
{
    public function __construct(protected Command $command, protected InputInterface $input)
    {
    }

    public function publish(): int
    {
        [$offered, $labels] = $this->collectOfferedFiles();

        $selected = Arr::only($offered, $this->selectFiles($offered, $labels));

        if ($selected === []) {
            $this->command->infoComment('No views selected; nothing to publish.');

            return Command::SUCCESS;
        }

        [$copy, $current, $blocked] = $this->decide($selected);

        $overwrite = $this->resolveBlocked($blocked);

        // A null resolution means the run stopped after the decision but before any write: a non-interactive
        // blocked run without --force is a hard failure, while an interactive Cancel is a clean exit.
        if ($overwrite === null) {
            return $this->canPrompt() ? Command::SUCCESS : Command::FAILURE;
        }

        $published = array_merge($copy, $overwrite);

        foreach ($published as $source => $target) {
            Filesystem::ensureParentDirectoryExists($target);
            Filesystem::copy($source, $target);
        }

        return $this->report($published, $current, $overwrite === [] ? $blocked : [], count($offered));
    }

    /**
     * @return array{0: array<string, string>, 1: array<string, string>}
     *              A tuple of [source => target] and [source => group-prefixed label] for the offered files.
     */
    protected function collectOfferedFiles(): array
    {
        $offered = [];
        $labels = [];

        foreach ($this->groups() as $key => $group) {
            foreach ($group->publishableFilesMap() as $source => $target) {
                $offered[$source] = $target;
                $labels[$source] = $key.'/'.Str::after($source, $group->source.'/');
            }
        }

        return [$offered, $labels];
    }

    /** @return array<string, \Hyde\Console\Helpers\ViewPublishGroup> The offered groups, keyed by short name and filtered by --layouts/--components. */
    protected function groups(): array
    {
        $groups = [
            'layouts' => ViewPublishGroup::fromGroup('hyde-layouts'),
            'components' => ViewPublishGroup::fromGroup('hyde-components'),
        ];

        if ($this->command->option('layouts')) {
            return Arr::only($groups, ['layouts']);
        }

        if ($this->command->option('components')) {
            return Arr::only($groups, ['components']);
        }

        return $groups;
    }

    /**
     * @param  array<string, string>  $offered
     * @param  array<string, string>  $labels
     * @return array<string>  The selected source keys.
     */
    protected function selectFiles(array $offered, array $labels): array
    {
        // --all skips the picker; so does a non-interactive run, where publishing a scoped group is
        // exactly equivalent to adding --all: one predictable rule, with OverwritePolicy still protecting
        // any modified files.
        if ($this->command->option('all') || ! $this->canPrompt()) {
            return array_keys($offered);
        }

        return InteractiveMultiselect::select('Select Hyde views to publish', $labels, 'All views');
    }

    /**
     * Decide every selected file's outcome up front, before anything is written.
     *
     * @param  array<string, string>  $selected
     * @return array{0: array<string, string>, 1: array<string, string>, 2: array<string, string>}
     *              A tuple of [copy, already-current, blocked] maps, each source => target.
     */
    protected function decide(array $selected): array
    {
        $copy = [];
        $current = [];
        $blocked = [];

        foreach ($selected as $source => $target) {
            match (OverwritePolicy::decide($source, $target)) {
                OverwriteAction::Copy => $copy[$source] = $target,
                OverwriteAction::Skip => $current[$source] = $target,
                OverwriteAction::Blocked => $blocked[$source] = $target,
            };
        }

        return [$copy, $current, $blocked];
    }

    /**
     * Resolve what to do with modified (blocked) files, after the full outcome is known but before any write.
     *
     * @param  array<string, string>  $blocked
     * @return array<string, string>|null  The blocked files to overwrite, or null when the run should stop
     *                                      (cancelled interactively, or blocked non-interactively without --force).
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

            foreach ($blocked as $target) {
                $this->command->line('  '.$target);
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

        return match ($choice) {
            'overwrite' => $blocked,
            'skip' => [],
            default => $this->cancel(),
        };
    }

    protected function cancel(): ?array
    {
        $this->command->infoComment('Cancelled. No views were published.');

        return null;
    }

    /**
     * @param  array<string, string>  $published  The files actually written (source => target).
     * @param  array<string, string>  $current    The files skipped because already up to date.
     * @param  array<string, string>  $blocked    The modified files left unchanged (interactive skip).
     */
    protected function report(array $published, array $current, array $blocked, int $offeredTotal): int
    {
        if ($published === [] && $blocked === [] && $current !== []) {
            $this->command->infoComment('All selected views are already up to date.');

            return Command::SUCCESS;
        }

        if ($published !== []) {
            $this->command->infoComment($this->publishedLine($published, $offeredTotal));
        }

        if ($current !== []) {
            $this->command->infoComment(sprintf('%s already up to date and skipped.', $this->viewCount(count($current))));
        }

        if ($blocked !== []) {
            $this->command->newLine();
            $this->command->warn(sprintf('%s left unchanged because they were modified:', $this->viewCount(count($blocked))));

            foreach ($blocked as $target) {
                $this->command->line('  '.$target);
            }

            $this->command->line('Run again with --force to overwrite.');
        }

        return Command::SUCCESS;
    }

    /** @param  array<string, string>  $published */
    protected function publishedLine(array $published, int $offeredTotal): string
    {
        $count = count($published);

        if ($count === 1) {
            return sprintf('Published 1 view to [%s]', reset($published));
        }

        // "all N" is reserved for when the entire offered set was genuinely copied; any file that was
        // already current or a blocked modification drops the count below the offered total.
        $base = $this->baseDirectory($published);

        return $count === $offeredTotal
            ? sprintf('Published all %d views to [%s]', $count, $base)
            : sprintf('Published %d views to [%s]', $count, $base);
    }

    protected function viewCount(int $count): string
    {
        return $count === 1 ? '1 view' : "$count views";
    }

    /** Find the most specific common parent directory shared by the given files' target paths. */
    protected function baseDirectory(array $files): string
    {
        $partsMap = collect($files)->map(fn (string $file): array => explode('/', $file));

        $commonParts = $partsMap->reduce(function (array $carry, array $parts): array {
            return array_intersect($carry, $parts);
        }, $partsMap->first());

        return implode('/', $commonParts);
    }

    protected function canPrompt(): bool
    {
        return ConsoleHelper::canUseLaravelPrompts($this->input);
    }
}
