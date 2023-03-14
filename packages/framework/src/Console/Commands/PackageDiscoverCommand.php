<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Hyde;
use Illuminate\Foundation\Console\PackageDiscoverCommand as BaseCommand;
use Illuminate\Foundation\PackageManifest;

/**
 */
class PackageDiscoverCommand extends BaseCommand
{
    /** @var true */
    protected $hidden = true;

    public function handle(PackageManifest $manifest): void
    {
        $manifest->manifestPath = Hyde::path('app/storage/framework/cache/packages.php');
        parent::handle($manifest);
    }
}
