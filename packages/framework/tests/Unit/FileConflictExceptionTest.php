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
    public function test_it_can_be_instantiated()
    {
        $this->assertInstanceOf(FileConflictException::class, new FileConflictException());
    }

    public function test_it_can_be_thrown()
    {
        $this->expectException(FileConflictException::class);

        throw new FileConflictException();
    }

    public function test_exception_code()
    {
        $this->assertSame(409, (new FileConflictException())->getCode());
    }

    public function test_exception_message()
    {
        $this->assertSame('A file already exists at this path.', (new FileConflictException())->getMessage());
    }

    public function test_exception_message_with_path()
    {
        HydeKernel::setInstance(new HydeKernel('my-base-path'));
        $this->assertSame('File [path/to/file] already exists.', (new FileConflictException('path/to/file'))->getMessage());
    }

    public function test_exception_message_with_absolute_path()
    {
        HydeKernel::setInstance(new HydeKernel('my-base-path'));
        $this->assertSame('File [path/to/file] already exists.', (new FileConflictException('my-base-path/path/to/file'))->getMessage());
    }

    public function test_exception_message_with_custom_message()
    {
        $this->assertSame('Custom message', (new FileConflictException(null, 'Custom message'))->getMessage());
    }

    public function test_exception_message_with_custom_message_and_path()
    {
        $this->assertSame('Custom message', (new FileConflictException('foo', 'Custom message'))->getMessage());
    }
}
