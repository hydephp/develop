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
}
