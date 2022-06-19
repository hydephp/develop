<?php

namespace Hyde\Framework\Actions;

use Hyde\Framework\Contracts\ActionContract;
use Hyde\Framework\Hyde;

/**
 * Checks if the installed config is up-to-date with the Framework's config.
 * Works by comparing the number of title blocks, which is a crude but fast way to check.
 *
 * @see \Hyde\Framework\Testing\Feature\Actions\ChecksIfConfigIsUpToDateTest
 * @deprecated v0.39.0-beta - Will be replaced by checking the version instead.
 */
class ChecksIfConfigIsUpToDate implements ActionContract
{
    public string $hydeConfig;
    public string $frameworkConfig;

    public function __construct()
    {
        $this->hydeConfig = file_get_contents(Hyde::path('config/hyde.php'));
        $this->frameworkConfig = file_get_contents(Hyde::vendorPath('config/hyde.php'));
    }

    public function execute(): bool
    {
        return $this->findOptions($this->hydeConfig) === $this->findOptions($this->frameworkConfig);
    }

    public function findOptions(string $config): int
    {
        return substr_count($config, '--------------------------------------------------------------------------');
    }
}
