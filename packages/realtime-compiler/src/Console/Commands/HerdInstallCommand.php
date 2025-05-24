<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler\Console\Commands;

use Hyde\Hyde;
use Hyde\Console\Concerns\Command;
use Illuminate\Support\Facades\File;
use Exception;

/**
 * @experimental This command is experimental and may be unstable. Report issues at GitHub!
 *
 * @see https://github.com/hydephp/realtime-compiler/pull/30
 */
class HerdInstallCommand extends Command
{
    /** @var string */
    protected $signature = 'herd:install {--force : Overwrite any existing file}';

    /** @var string */
    protected $description = '[Experimental] Install the HydePHP Valet driver for Laravel Herd';

    public function safeHandle(): int
    {
        $driverTargetPath = $this->getDriverTargetPath();
        $driverSourcePath = Hyde::vendorPath('resources/stubs/HydeValetDriver.php', 'realtime-compiler');

        if (! is_dir(dirname($driverTargetPath))) {
            $this->error('Herd Valet drivers directory not found. Is Herd installed?');

            return Command::FAILURE;
        }

        if (file_exists($driverTargetPath) && ! $this->option('force')) {
            if (! $this->confirm('The HydePHP Valet driver for Herd already exists. Do you want to overwrite it?', true)) {
                $this->info('Installation cancelled.');

                return Command::SUCCESS;
            }
        }

        try {
            File::copy($driverSourcePath, $driverTargetPath);
            $this->info('HydePHP Valet driver for Herd successfully installed!');
            $this->line('Driver path: '.$driverTargetPath);

            return Command::SUCCESS;
        } catch (Exception $exception) {
            $this->error('Failed to install the HydePHP Valet driver: '.$exception->getMessage());

            return Command::FAILURE;
        }
    }

    private function getDriverTargetPath(): string
    {
        return match (PHP_OS_FAMILY) {
            'Darwin' => $_SERVER['HOME'].'/Library/Application Support/Herd/config/valet/Drivers/HydeValetDriver.php',
            'Windows' => $_SERVER['USERPROFILE'].'\.config\herd\config\valet\Drivers\HydeValetDriver.php',
            default => throw new Exception('Herd is not yet supported on Linux.'),
        };
    }
}
