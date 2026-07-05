<?php

declare(strict_types=1);

namespace Hyde\Console\Helpers;

use Hyde\Console\Concerns\Command;
use Hyde\Enums\OverwriteAction;
use Hyde\Facades\Filesystem;
use Hyde\Framework\Services\OverwritePolicy;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;

use function count;
use function Hyde\unixsum_file;
use function sprintf;
use function Laravel\Prompts\select;

/**
 * @internal Shared conflict resolution for publish command helpers.
 */
abstract class BasePublisher
{
    protected bool $writeRefreshFailed = false;

    public function __construct(protected Command $command, protected InputInterface $input)
    {
    }

    /**
     * Re-check destinations immediately before copying so a file changed during an interactive prompt is not lost.
     *
     * @param  array<array{source: string, target: string, absolute: string, destinationChecksum?: string}>  $copy
     * @param  array<array{source: string, target: string, absolute: string, destinationChecksum?: string}>  $overwrite
     * @return array<array{source: string, target: string, absolute: string, destinationChecksum?: string}>|null
     */
    protected function refreshApprovedWrites(array $copy, array $overwrite): ?array
    {
        $this->writeRefreshFailed = false;
        $written = [];

        foreach ($copy as $record) {
            $record = $this->refreshApprovedWrite($record, false);

            if ($record === null) {
                return null;
            }

            if ($record !== []) {
                $written[] = $record;
            }
        }

        foreach ($overwrite as $record) {
            $record = $this->refreshApprovedWrite($record, true);

            if ($record === null) {
                return null;
            }

            if ($record !== []) {
                $written[] = $record;
            }
        }

        return $written;
    }

    /**
     * @param  array{source: string, target: string, absolute: string, destinationChecksum?: string}  $record
     * @return array{source: string, target: string, absolute: string, destinationChecksum?: string}|array{}|null
     */
    protected function refreshApprovedWrite(array $record, bool $approvedOverwrite): ?array
    {
        return match (OverwritePolicy::decide($record['source'], $record['absolute'])) {
            OverwriteAction::Copy => $record,
            OverwriteAction::Skip => $this->skipCurrentWrite($record),
            OverwriteAction::Blocked => $this->handleStillBlockedWrite($record, $approvedOverwrite),
        };
    }

    /**
     * @param  array{source: string, target: string, absolute: string, destinationChecksum?: string}  $record
     * @return array{}
     */
    protected function skipCurrentWrite(array $record): array
    {
        $this->noteCurrent($record);

        return [];
    }

    /**
     * @param  array{source: string, target: string, absolute: string, destinationChecksum?: string}  $record
     * @return array{source: string, target: string, absolute: string, destinationChecksum?: string}|null
     */
    protected function handleStillBlockedWrite(array $record, bool $approvedOverwrite): ?array
    {
        if (! $approvedOverwrite || ($record['destinationChecksum'] ?? null) !== $this->destinationChecksum($record['absolute'])) {
            $this->writeRefreshFailed = true;
            $this->command->error(sprintf('Cannot publish: destination [%s] changed after overwrite checks. Run the command again.', $record['target']));

            return null;
        }

        return $record;
    }

    /**
     * @param  array<array{source: string, target: string, absolute: string, destinationChecksum?: string}>  $blocked
     * @return array<array{source: string, target: string, absolute: string, destinationChecksum?: string}>|null
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

        return match ($choice) {
            'overwrite' => $blocked,
            'skip' => [],
            default => $this->cancel(),
        };
    }

    protected function cancel(): ?array
    {
        $this->command->infoComment($this->cancelledMessage());

        return null;
    }

    protected function copy(string $source, string $target): void
    {
        Filesystem::ensureParentDirectoryExists($target);

        if (! Filesystem::copy($source, $target)) {
            throw new RuntimeException("Failed to copy [$source] to [$target].");
        }
    }

    protected function destinationChecksum(string $target): string
    {
        return unixsum_file($target);
    }

    protected function failedWriteRefresh(): bool
    {
        return $this->writeRefreshFailed;
    }

    /**
     * @param  array{source: string, target: string, absolute: string, destinationChecksum?: string}  $record
     */
    protected function noteCurrent(array $record): void
    {
    }

    protected function canPrompt(): bool
    {
        return ConsoleHelper::canUseLaravelPrompts($this->input);
    }

    abstract protected function cancelledMessage(): string;
}
