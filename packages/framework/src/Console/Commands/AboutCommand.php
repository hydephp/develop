<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Hyde;
use Hyde\Facades\Config;
use Composer\InstalledVersions;
use Hyde\Foundation\PharSupport;
use Illuminate\Foundation\Console\AboutCommand as BaseAboutCommand;
use Illuminate\Support\Composer;

use function str_replace;
use function realpath;
use function app;
use function get_included_files;

/**
 * Print debug information.
 */
class AboutCommand extends BaseAboutCommand
{
    /** @var string */

    /** @var string */
    protected $description = 'Print debug information';

    public function __construct(Composer $composer)
    {
        parent::__construct($composer);

        if (Config::getString('app.env', 'production') !== 'development') {
            $this->setHidden();
        }
    }

    protected function printVerbosePathInformation(): void
    {
        static::addToSection('Project directory', ' > '.realpath(Hyde::path()));

        static::addToSection('Framework vendor path', function () {
            $vendorPath = str_replace('/', DIRECTORY_SEPARATOR, Hyde::vendorPath());
            $realPath = realpath(Hyde::vendorPath());

            return [
                'vendor' => ' > '.$vendorPath,
                'real' => ' > '.$realPath,
            ];
        });
    }

    protected function printEnabledFeatures(): void
    {
        /** @var array<\Hyde\Enums\Feature> $features */
        $features = Config::getArray('hyde.features', []);
        $formatEnabledStatus = fn ($value) => $value ? '<fg=yellow;options=bold>ENABLED</>' : 'OFF';

        static::addToSection('Enabled features', function () use ($features, $formatEnabledStatus) {
            if (empty($features)) {
                return 'None';
            }

            $enabledFeatures = [];

            foreach ($features as $feature) {
                $enabledFeatures[$feature->name] = static::format(true, console: $formatEnabledStatus);
            }

            return $enabledFeatures;
        });

    }

    /**
     * Gather information about the application.
     *
     * @return void
     */
    protected function gatherApplicationInformation()
    {
        self::$data = [];

        static::addToSection('Hyde Info', fn () => [
            'Hyde Version' => ((InstalledVersions::isInstalled('hyde/hyde') ? InstalledVersions::getPrettyVersion('hyde/hyde') : null) ?: 'unreleased'),
            'Framework Version' => (InstalledVersions::getPrettyVersion('hyde/framework') ?: 'unreleased')
        ]);

        static::addToSection('Environment', fn () => [
            'App Env' => ((InstalledVersions::isInstalled('hyde/hyde') ? InstalledVersions::getPrettyVersion('hyde/hyde') : null) ?: 'unreleased')
        ]);

        if ($this->output->isVerbose()) {
            $this->printVerbosePathInformation();
        } else {
            static::addToSection('Project directory', Hyde::path());

            if (PharSupport::running()) {
                static::addToSection('Application binary path', get_included_files()[0]);
            }
        }

        $this->printEnabledFeatures();
    }
}
