<?php

declare(strict_types=1);

namespace Hyde\Framework\Concerns;

use Hyde\Hyde;
use Hyde\Framework\Exceptions\FileNotFoundException;
use function file_exists;

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
     * @throws FileNotFoundException If the file does not exist.
     */
    public static function validateExistence(string $model, string $identifier): void
    {
        /** @var \Hyde\Pages\Concerns\HydePage $model */
        $filepath = $model::sourcePath($identifier);

        if (! file_exists(Hyde::path($filepath))) {
            throw new FileNotFoundException($filepath);
        }
    }
}
