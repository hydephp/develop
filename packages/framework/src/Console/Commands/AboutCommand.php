<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Illuminate\Foundation\Console\AboutCommand as BaseAboutCommand;

/**
 * Print about information.
 */
class AboutCommand extends BaseAboutCommand
{
    protected $description = 'Display basic information about your HydePHP project';
}
