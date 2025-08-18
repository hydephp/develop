<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Foundation\HydeKernel;
use Hyde\Framework\Exceptions\FileConflictException;
use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Framework\Exceptions\FileConflictException
 */
class FileConflictExceptionTest extends UnitTestCase
{
    public function testItCanBeInstantiated()
    {
        $this->assertInstanceOf(FileConflictException::class, new FileConflictException());
    }

    public function testItCanBeThrown()
    {
        $this->expectException(FileConflictException::class);

        throw new FileConflictException();
    }

    public function testExceptionCode()
    {
        $this->assertSame(409, (new FileConflictException())->getCode());
    }

    public function testExceptionMessage()
    {
        $this->assertSame('A file already exists at this path.', (new FileConflictException())->getMessage());
    }

    public function testExceptionMessageWithPath()
    {
        HydeKernel::setInstance(new HydeKernel('my-base-path'));
        $this->assertSame('File [path/to/file] already exists.', (new FileConflictException('path/to/file'))->getMessage());
    }

    public function testExceptionMessageWithAbsolutePath()
    {
        HydeKernel::setInstance(new HydeKernel('my-base-path'));
        $this->assertSame('File [path/to/file] already exists.', (new FileConflictException('my-base-path/path/to/file'))->getMessage());
    }
}
