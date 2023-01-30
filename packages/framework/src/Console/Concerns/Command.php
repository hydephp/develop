<?php

declare(strict_types=1);

namespace Hyde\Console\Concerns;

use Hyde\Hyde;
use JetBrains\PhpStorm\Deprecated;
use LaravelZero\Framework\Commands\Command as BaseCommand;

/**
 * @see \Hyde\Framework\Testing\Feature\CommandTest
 */
abstract class Command extends BaseCommand
{
    /**
     * Create a filepath that can be opened in the browser from a terminal.
     */
    public static function createClickableFilepath(string $filepath): string
    {
        return 'file://'.str_replace('\\', '/', realpath($filepath) ?: Hyde::path($filepath));
    }

    /**
     * Write a nicely formatted and consistent message to the console. Using InfoComment for a lack of a better term.
     * @deprecated Use the dynamicInfoComment() method instead
     */
    #[Deprecated('Use the dynamicInfoComment() method instead', replacement: '$this->dynamicInfoComment("%parameter0% [%parameter1%] %parameter2%")' )]
    public function infoComment(string $info, string $comment, ?string $moreInfo = null): void
    {
        $this->line("<info>$info</info> [<comment>$comment</comment>]".($moreInfo ? " <info>$moreInfo</info>" : ''));
    }

    /**
     * Dynamically create an infoComment from a single string.
     */
    public function dynamicInfoComment(string $string): void
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
