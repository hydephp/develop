<?php

declare(strict_types=1);

namespace Hyde\Console\Helpers;

use Hyde\Console\Concerns\Command;
use Hyde\Enums\OverwriteAction;
use Hyde\Framework\Services\OverwritePolicy;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use function array_keys;
use function array_merge;
use function count;
use function explode;
use function implode;
use function reset;
use function sprintf;

/**
 * @internal This helper is scoped to the publish command and should not be used elsewhere.
 */
class ViewsPublisher extends BasePublisher
{
    /** @var array<string, string> Files skipped because already up to date. */
    protected array $current = [];

    public function __construct(protected PublisherConsole $console)
    {
    }

    /**
     * Every file's outcome is decided, and conflicts resolved, before anything is written — so cancelling
     * never leaves a half-published tree.
     */
    public function publish(): int
    {
        [$offered, $labels] = $this->collectOfferedFiles();

        $selected = Arr::only($offered, $this->selectFiles($offered, $labels));

        if ($selected === []) {
            $this->console->infoComment('No views selected; nothing to publish.');

            return Command::SUCCESS;
        }

        [$copy, $current, $blocked] = $this->decide($selected);
        $this->current = $current;

        $overwrite = $this->console->resolveBlocked($blocked, 'Cancelled. No views were published.');

        // A null resolution means the run stopped after the decision but before any write: a non-interactive
        // blocked run without --force is a hard failure, while an interactive Cancel is a clean exit.
        if ($overwrite === null) {
            return $this->console->canPrompt() ? Command::SUCCESS : Command::FAILURE;
        }

        $written = array_merge($copy, $overwrite);

        $published = $this->recordsToMap($written);

        foreach ($published as $source => $target) {
            $this->copy($source, $target);
        }

        $status = $this->report($published, $this->current, $overwrite === [] ? $this->recordsToMap($blocked) : [], count($offered));

        return $this->hasPolicyErrors() ? Command::FAILURE : $status;
    }

    /**
     * @return array{0: array<string, string>, 1: array<string, string>} A tuple of [source => target] and [source => group-prefixed label] for the offered files.
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

        if ($this->console->option('layouts')) {
            return Arr::only($groups, ['layouts']);
        }

        if ($this->console->option('components')) {
            return Arr::only($groups, ['components']);
        }

        return $groups;
    }

    /**
     * @param  array<string, string>  $offered
     * @param  array<string, string>  $labels
     * @return array<string> The selected source keys.
     */
    protected function selectFiles(array $offered, array $labels): array
    {
        // --all skips the picker; so does a non-interactive run, where publishing a scoped group is
        // exactly equivalent to adding --all: one predictable rule, with OverwritePolicy still protecting
        // any modified files.
        if ($this->console->option('all') || ! $this->console->canPrompt()) {
            return array_keys($offered);
        }

        return InteractiveMultiselect::select('Select Hyde views to publish', $labels, 'All views');
    }

    /**
     * @param  array<string, string>  $selected
     * @return array{0: array<array{source: string, target: string, absolute: string}>, 1: array<string, string>, 2: array<array{source: string, target: string, absolute: string}>} A tuple of [copy records, already-current map, blocked records].
     */
    protected function decide(array $selected): array
    {
        $copy = [];
        $current = [];
        $blocked = [];

        foreach ($selected as $source => $target) {
            $action = OverwritePolicy::decide($source, $target);
            $record = $this->record($source, $target);

            if ($action === OverwriteAction::Copy) {
                $copy[] = $record;
            } elseif ($action === OverwriteAction::Skip) {
                $current[$source] = $target;
            } elseif ($action === OverwriteAction::Error) {
                $this->reportPolicyError($this->console, $source, $target);
            } else {
                $blocked[] = $record;
            }
        }

        return [$copy, $current, $blocked];
    }

    /** @return array{source: string, target: string, absolute: string} */
    protected function record(string $source, string $target): array
    {
        return ['source' => $source, 'target' => $target, 'absolute' => $target];
    }

    /** @param  array<array{source: string, target: string, absolute: string}>  $records */
    protected function recordsToMap(array $records): array
    {
        $map = [];

        foreach ($records as $record) {
            $map[$record['source']] = $record['target'];
        }

        return $map;
    }

    /**
     * @param  array<string, string>  $published  The files actually written (source => target).
     * @param  array<string, string>  $current  The files skipped because already up to date.
     * @param  array<string, string>  $blocked  The modified files left unchanged (interactive skip).
     */
    protected function report(array $published, array $current, array $blocked, int $offeredTotal): int
    {
        if ($published === [] && $blocked === [] && $current !== []) {
            $this->console->infoComment('All selected views are already up to date.');

            return Command::SUCCESS;
        }

        if ($published !== []) {
            $this->console->infoComment($this->publishedLine($published, $offeredTotal));
        }

        if ($current !== []) {
            $this->console->infoComment(sprintf('%s already up to date and skipped.', $this->viewCount(count($current))));
        }

        if ($blocked !== []) {
            $this->console->newLine();
            $this->console->warn(sprintf('%s left unchanged because they were modified:', $this->viewCount(count($blocked))));

            foreach ($blocked as $target) {
                $this->console->line('  '.$target);
            }

            $this->console->line('Run again with --force to overwrite.');
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
        $commonParts = [];

        foreach ($partsMap->first() as $index => $part) {
            foreach ($partsMap as $parts) {
                if (! isset($parts[$index]) || $parts[$index] !== $part) {
                    break 2;
                }
            }

            $commonParts[] = $part;
        }

        return implode('/', $commonParts);
    }
}
