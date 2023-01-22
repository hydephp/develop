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
    /**
     * Create a filepath that can be opened in the browser from a terminal.
     */
    public static function createClickableFilepath(string $filepath): string
    {
        return 'file://'.str_replace('\\', '/', realpath($filepath) ?: Hyde::path($filepath));
    }

    /**
     * Write a nicely formatted and consistent message to the console. Using InfoComment for a lack of a better term.
     */
    public function infoComment(string $info, string $comment, ?string $moreInfo = null): void
    {
        $this->line("<info>$info</info> [<comment>$comment</comment>]".($moreInfo ? " <info>$moreInfo</info>" : ''));
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
