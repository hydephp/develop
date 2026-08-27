<?php

declare(strict_types=1);

namespace Hyde\Console\Helpers;

use Hyde\Console\Concerns\Command;
use Symfony\Component\Console\Input\InputInterface;

use function count;
use function reset;
use function sprintf;
use function Laravel\Prompts\select;

/**
 * @internal The console boundary shared by the publish command helpers.
 */
class PublisherConsole
{
    public function __construct(protected Command $command, protected InputInterface $input)
    {
    }

    public function option(string $key): mixed
    {
        return $this->command->option($key);
    }

    public function canPrompt(): bool
    {
        return ConsoleHelper::canUseLaravelPrompts($this->input);
    }

    public function error(string $message): void
    {
        $this->command->error($message);
    }

    public function warn(string $message): void
    {
        $this->command->warn($message);
    }

    public function line(string $message): void
    {
        $this->command->line($message);
    }

    public function newLine(): void
    {
        $this->command->newLine();
    }

    public function infoComment(string $message): void
    {
        $this->command->infoComment($message);
    }

    /**
     * Decide what to do with the files that the overwrite policy blocked because the user has modified them.
     *
     * @param  array<array{source: string, target: string, absolute: string}>  $blocked
     * @return array<array{source: string, target: string, absolute: string}>|null The records to overwrite, or null when the run should stop.
     */
    public function resolveBlocked(array $blocked, string $cancelledMessage): ?array
    {
        if ($blocked === []) {
            return [];
        }

        if ($this->option('force')) {
            return $blocked;
        }

        if (! $this->canPrompt()) {
            $this->error('Cannot overwrite modified files without --force:');

            foreach ($blocked as $record) {
                $this->line('  '.$record['target']);
            }

            $this->newLine();
            $this->line('Run again with --force to overwrite.');

            return null;
        }

        $single = count($blocked) === 1;

        $choice = select($this->warnAboutModifiedFiles($blocked), [
            'skip' => $single ? 'Keep the existing file' : 'Keep the modified files',
            'overwrite' => $single ? 'Overwrite it with the Hyde version' : 'Overwrite them with the Hyde versions',
            'cancel' => 'Cancel',
        ], 'skip');

        return match ($choice) {
            'overwrite' => $blocked,
            'skip' => [],
            default => $this->cancel($cancelledMessage),
        };
    }

    /**
     * Warn about the files that would lose local changes, and return the one-line label for the choice prompt.
     *
     * @param  array<array{source: string, target: string, absolute: string}>  $blocked
     */
    protected function warnAboutModifiedFiles(array $blocked): string
    {
        if (count($blocked) === 1) {
            return sprintf('%s has local changes. Publishing will overwrite them.', reset($blocked)['target']);
        }

        $this->warn(sprintf('%d destination files have local changes:', count($blocked)));

        foreach ($blocked as $record) {
            $this->line('  '.$record['target']);
        }

        $this->newLine();

        return 'Publishing will overwrite those changes.';
    }

    protected function cancel(string $message): ?array
    {
        $this->infoComment($message);

        return null;
    }
}
