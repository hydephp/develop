<?php

declare(strict_types=1);

namespace Hyde\Console\Concerns;

use Exception;
use Hyde\Hyde;
use LaravelZero\Framework\Commands\Command as BaseCommand;

use function config;
use function sprintf;

/**
 * @see \Hyde\Framework\Testing\Feature\CommandTest
 */
abstract class Command extends BaseCommand
{
    public const USER_EXIT = 130;

    /**
     * @return int The exit code.
     */
    public function handle(): int
    {
        try {
            return $this->safeHandle();
        } catch (Exception $exception) {
            return $this->handleException($exception);
        }
    }

    /**
     * This method can be overridden by child classes to provide automatic exception handling.
     * Existing code can be converted simply by renaming the handle() method to safeHandle().
     *
     * @return int The exit code.
     */
    protected function safeHandle(): int
    {
        return Command::SUCCESS;
    }

    /**
     * Handle an exception that occurred during command execution.
     *
     * @param  string|null  $file  The file where the exception occurred. Leave null to auto-detect.
     * @return int The exit code
     */
    public function handleException(Exception $exception, ?string $file = null, ?int $line = null): int
    {
        // When testing it might be more useful to see the full stack trace, so we have an option to actually throw the exception.
        if (config('app.throw_on_console_exception', false)) {
            throw $exception;
        }

        // If the exception was thrown from the same file as a command, then we don't need to show which file it was thrown from.
        if (str_ends_with($file ?? $exception->getFile(), 'Command.php')) {
            $this->error("Error: {$exception->getMessage()}");
        } else {
            $this->error(sprintf('Error: %s at ', $exception->getMessage()).sprintf('%s:%s', $file ?? $exception->getFile(), $line ?? $exception->getLine()));
        }

        return Command::FAILURE;
    }

    /**
     * Create a filepath that can be opened in the browser from a terminal.
     */
    public static function createClickableFilepath(string $filepath): string
    {
        return 'file://'.str_replace('\\', '/', realpath($filepath) ?: Hyde::path($filepath));
    }

    /**
     * Write a nicely formatted and consistent message to the console. Using InfoComment for a lack of a better term.
     *
     * Text in [brackets] will automatically be wrapped in <comment> tags.
     */
    public function infoComment(string $string): void
    {
        $replacements = [
            '[' => '</info>[<comment>',
            ']' => '</comment>]<info>',
        ];

        $string = str_replace(array_keys($replacements), array_values($replacements), $string);

        $this->line("<info>$string</info>");
    }

    /** @experimental This method may change (or be removed) before the 1.0.0 release */
    public function gray(string $string): void
    {
        $this->line($this->inlineGray($string));
    }

    /** @experimental This method may change (or be removed) before the 1.0.0 release */
    public function inlineGray(string $string): string
    {
        return "<fg=gray>$string</>";
    }

    public function indentedLine(int $indent, string $string): void
    {
        $this->line(str_repeat(' ', $indent).$string);
    }
}
