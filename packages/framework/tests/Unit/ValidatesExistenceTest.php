<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Testing\UnitTestCase;
use Illuminate\Support\Facades\File;
use Hyde\Framework\Concerns\ValidatesExistence;
use Hyde\Framework\Exceptions\FileNotFoundException;
use Hyde\Pages\BladePage;

/**
 * @covers \Hyde\Framework\Concerns\ValidatesExistence
 * @covers \Hyde\Framework\Exceptions\FileNotFoundException
 */
class ValidatesExistenceTest extends UnitTestCase
{
    protected static bool $needsKernel = true;

    public function testValidateExistenceDoesNothingIfFileExists()
    {
        $class = new ValidatesExistenceTestClass();

        $class->run(BladePage::class, 'index');

        $this->assertTrue(true);
    }

    public function testValidateExistenceThrowsFileNotFoundExceptionIfFileDoesNotExist()
    {
        File::shouldReceive('missing')->andReturn(true);

        $this->expectException(FileNotFoundException::class);

        $class = new ValidatesExistenceTestClass();

        $class->run(BladePage::class, 'not-found');
    }
}

class ValidatesExistenceTestClass
{
    use ValidatesExistence;

    public function run(...$args): void
    {
        $this->validateExistence(...$args);
    }
}
