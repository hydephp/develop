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
    protected static bool $isUpToDate;

    public function execute(): bool
    {
        if (! isset(self::$isUpToDate)) {
            self::$isUpToDate = $this->isUpToDate();
        }

        return self::$isUpToDate;
    }

    protected function isUpToDate(): bool
    {
        return $this->findOptions(
            file_get_contents(Hyde::path('config/hyde.php'))
        ) === $this->findOptions(
            file_get_contents(Hyde::vendorPath('config/hyde.php'))
        );
    }

    public function findOptions(string $config): int
    {
        return substr_count($config, '--------------------------------------------------------------------------');
    }
}
