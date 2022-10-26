<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Framework\Concerns\ValidatesExistence;
use Hyde\Framework\Exceptions\FileNotFoundException;
use Hyde\Framework\Models\Pages\BladePage;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Framework\Concerns\ValidatesExistence
 * @covers \Hyde\Framework\Exceptions\FileNotFoundException
 */
class ValidatesExistenceTest extends TestCase
{
    public function test_validate_existence_does_nothing_if_file_exists()
    {
        $class = new class
        {
            use ValidatesExistence;
        };

        $class->validateExistence(BladePage::class, 'index');

        $this->assertTrue(true);
    }

    public function test_validate_existence_throws_file_not_found_exception_if_file_does_not_exist()
    {
        $this->expectException(FileNotFoundException::class);

        $class = new class
        {
            use ValidatesExistence;
        };

        $class->validateExistence(BladePage::class, 'not-found');
    }
}
