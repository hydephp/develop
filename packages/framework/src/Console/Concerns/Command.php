<?php

declare(strict_types=1);

namespace Hyde\Console\Concerns;

use Exception;
use Hyde\Hyde;
use Hyde\Facades\Config;
use LaravelZero\Framework\Commands\Command as BaseCommand;

use function array_keys;
use function array_values;
use function realpath;
use function sprintf;
use function str_repeat;
use function str_replace;

/**
 * A base class for HydeCLI command that adds some extra functionality and output
 * helpers to reduce repeated code and to provide a consistent user interface.
 *
 */
abstract class Command extends BaseCommand
{
    final public const USER_EXIT = 130;

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
        if (Config::getBool('app.throw_on_console_exception', false)) {
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
     *
     * @param  string|null  $label  If provided, the link will be wrapped in a Symfony Console `href` tag.
     *                              Note that not all terminals support this, and it may lead to only
     *                              the label being shown, and the path being lost to the void.
     */
    public static function fileLink(string $path, string $label = null): string
    {
        $link = 'file://'.str_replace('\\', '/', realpath($path) ?: Hyde::path($path));

        return $label ? "<href=$link>$label</>" : $link;
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

    /** Write a grey-coloured line */
    public function gray(string $string): void
    {
        $this->line($this->inlineGray($string));
    }

    /** @internal This method may be confused with the ->gray method and may be removed */
    public function inlineGray(string $string): string
    {
        return "<fg=gray>$string</>";
    }

    /** @internal This method may be removed and should not be relied upon */
    public function href(string $link, string $label): string
    {
        return "<href=$link>$label</>";
    }

    /** Write a line with the specified indentation level */
    public function indentedLine(int $spaces, string $string): void
    {
        $this->line(str_repeat(' ', $spaces).$string);
    }
}
