<?php

declare(strict_types=1);

namespace Hyde\Console\Concerns;

use LaravelZero\Framework\Commands\Command as BaseCommand;

/**
 * @see \Hyde\Framework\Testing\Feature\CommandTest
 */
abstract class Command extends BaseCommand
{
    /**
     * Create a filepath that can be opened in the browser from a terminal.
     *
     * @todo Add option to treat path as already validated so paths that are not created yet can be printed?
     */
    public static function createClickableFilepath(string $filepath): string
    {
        if (realpath($filepath) === false) {
            return $filepath;
        }

        return 'file://'.str_replace('\\', '/', realpath($filepath));
    }
}
