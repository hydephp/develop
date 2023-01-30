<?php

declare(strict_types=1);

namespace Hyde\Console\Concerns;

use Hyde\Hyde;
use LaravelZero\Framework\Commands\Command as BaseCommand;

/**
 * @see \Hyde\Framework\Testing\Feature\CommandTest
 */
abstract class Command extends BaseCommand
{
    public const USER_EXIT = 130;

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
