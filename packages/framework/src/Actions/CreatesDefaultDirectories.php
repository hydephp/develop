<?php

namespace Hyde\Framework\Actions;

use Hyde\Framework\Hyde;
use JetBrains\PhpStorm\Pure;

/**
 * Create the default directories required by the Application.
 *
 * @deprecated as Hyde is bringing support for changing the default directories.
 *              The directories needed will be created (if needed) when the filesystem
 *              needs to access them. The default directories may be created in the TestCase.
 *
 * To prevent any issues with file generations, this action
 * is automatically whenever the application is booted.
 *
 * This behavior is handled in the Service Provider.
 * @see \Hyde\Framework\HydeServiceProvider
 */
class CreatesDefaultDirectories
{
    /**
     * The directories required by the application.
     *
     * @var array
     */
    protected array $requiredDirectories = [
        '_pages',
        '_posts',
        '_media',
        '_docs',
        '_site',
        '_site/posts',
        '_site/media',
        '_site/docs',
    ];

    /**
     * Execute the action.
     *
     * @return void
     */
    public function __invoke(): void
    {
        foreach ($this->requiredDirectories as $directory) {
            // Does the directory exist?      // Otherwise, create it.
            is_dir(Hyde::path($directory)) || mkdir(Hyde::path($directory), recursive: true);
        }
    }

    /**
     * Retrieve the array of default directories.
     *
     * @return array
     */
    #[Pure]
    public static function getRequiredDirectories(): array
    {
        return (new CreatesDefaultDirectories)->requiredDirectories;
    }
}
