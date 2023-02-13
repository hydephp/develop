<?php

declare(strict_types=1);

namespace Hyde\Console\Concerns;

use function config;
use Exception;
use Hyde\Hyde;
use LaravelZero\Framework\Commands\Command as BaseCommand;
use function sprintf;

/**
 * @see \Hyde\Framework\Testing\Feature\CommandTest
 */
abstract class Command extends BaseCommand
{
    public const USER_EXIT = 130;

    /**
     * The base handle method that can be overridden by child classes.
     *
     * Alternatively, implement the safeHandle method in your child class
     * to utilize the automatic exception handling provided by this method.
     *
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
     *
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
     * @return int The exit code
     */
    public function handleException(Exception $exception): int
    {
        // When testing it might be more useful to see the full stack trace, so we have an option to actually throw the exception.
        if (config('app.throw_on_console_exception', false)) {
            throw $exception;
        }

        // If the exception was thrown from the same file as a command, then we don't need to show which file it was thrown from.
        $location = str_ends_with($exception->getFile(), 'Command.php') ? '' : sprintf(' at %s:%s',
            $exception->getFile(), $exception->getLine()
        );
        $this->error("Error: {$exception->getMessage()}".$location);

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

    public function gray(string $string): void
    {
        $this->line($this->inlineGray($string));
    }

    public function inlineGray(string $string): string
    {
        return "<fg=gray>$string</>";
    }

    public function indentedLine(int $indent, string $string): void
    {
        $this->line(str_repeat(' ', $indent).$string);
    }
}
