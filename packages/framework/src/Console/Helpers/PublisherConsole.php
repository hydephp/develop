<?php

declare(strict_types=1);

namespace Hyde\Console\Helpers;

use Hyde\Console\Concerns\Command;
use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Input\InputInterface;

use function count;
use function sprintf;
use function Laravel\Prompts\select;

/**
 * @internal Console boundary for publish helpers.
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

    public function getOutput(): OutputStyle
    {
        return $this->command->getOutput();
    }

    /**
     * @param  array<array{source: string, target: string, absolute: string}>  $blocked
     * @return array<array{source: string, target: string, absolute: string}>|null
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

        $choice = select(sprintf('%d selected files already exist and appear modified.', count($blocked)), [
            'skip' => 'Skip modified files',
            'overwrite' => 'Overwrite modified files',
            'cancel' => 'Cancel',
        ], 'skip');

        return match ($choice) {
            'overwrite' => $blocked,
            'skip' => [],
            default => $this->cancel($cancelledMessage),
        };
    }

    protected function cancel(string $message): ?array
    {
        $this->infoComment($message);

        return null;
    }
}
