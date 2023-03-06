<?php

declare(strict_types=1);

namespace Hyde\Framework\Concerns;

use Hyde\Facades\Filesystem;
use Hyde\Framework\Exceptions\FileNotFoundException;

/**
 * Validate the existence of a Page model's source file.
 *
 * @see \Hyde\Framework\Testing\Unit\ValidatesExistenceTest
 */
trait ValidatesExistence
{
    /**
     * Check if a supplied source file exists or throw an exception.
     *
     * @param  class-string<\Hyde\Pages\Concerns\HydePage>  $model
     *
     * @throws FileNotFoundException If the file does not exist.
     */
    protected static function validateExistence(string $model, string $identifier): void
    {
        $filepath = $model::sourcePath($identifier);

        if (Filesystem::missing($filepath)) {
            throw new FileNotFoundException($filepath);
        }
    }
}
