<?php
declare(strict_types=1);

namespace Hyde\Framework\Concerns;

use Hyde\Framework\Exceptions\FileNotFoundException;
use Hyde\Framework\Hyde;

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
    public function validateExistence(string $model, string $slug): void
    {
        /** @var \Hyde\Framework\Concerns\HydePage $model */
        $filepath = $model::sourceDirectory().'/'.
            $slug.$model::fileExtension();

        if (! file_exists(Hyde::path($filepath))) {
            throw new FileNotFoundException($filepath);
        }
    }
}
