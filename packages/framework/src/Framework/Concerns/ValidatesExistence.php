<?php

declare(strict_types=1);

namespace Hyde\Framework\Concerns;

use Hyde\Facades\Filesystem;
use Hyde\Framework\Exceptions\FileNotFoundException;

/**
 * Validate the existence of a Page class's source file.
 */
trait ValidatesExistence
{
    /**
     * Check if a supplied source file exists or throw an exception.
     *
     * @param  class-string<\Hyde\Pages\Concerns\HydePage>  $pageClass
     *
     * @throws FileNotFoundException If the file does not exist.
     */
    protected static function validateExistence(string $pageClass, string $identifier): void
    {
        $path = $pageClass::sourcePath($identifier);

        if (Filesystem::missing($path)) {
            throw new FileNotFoundException($path);
        }
    }
}
