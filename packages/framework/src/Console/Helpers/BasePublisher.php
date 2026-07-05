<?php

declare(strict_types=1);

namespace Hyde\Console\Helpers;

use Hyde\Console\Concerns\Command;
use Hyde\Facades\Filesystem;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;

use function count;
use function sprintf;
use function Laravel\Prompts\select;

/**
 * @internal Shared conflict resolution for publish command helpers.
 */
abstract class BasePublisher
{
    public function __construct(protected Command $command, protected InputInterface $input)
    {
    }

    /**
     * @param  array<array{source: string, target: string, absolute: string}>  $blocked
     * @return array<array{source: string, target: string, absolute: string}>|null
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

    protected function canPrompt(): bool
    {
        return ConsoleHelper::canUseLaravelPrompts($this->input);
    }

    abstract protected function cancelledMessage(): string;
}
