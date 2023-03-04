<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Testing\UnitTestCase;
use Hyde\Framework\Exceptions\FileConflictException;
use Hyde\Framework\Exceptions\FileNotFoundException;
use Hyde\Framework\Exceptions\RouteNotFoundException;
use Hyde\Framework\Exceptions\BaseUrlNotSetException;
use Hyde\Framework\Exceptions\UnsupportedPageTypeException;

/**
 * @covers \Hyde\Framework\Exceptions\FileConflictException
 * @covers \Hyde\Framework\Exceptions\FileNotFoundException
 * @covers \Hyde\Framework\Exceptions\RouteNotFoundException
 * @covers \Hyde\Framework\Exceptions\BaseUrlNotSetException
 * @covers \Hyde\Framework\Exceptions\UnsupportedPageTypeException
 */
class CustomExceptionsTest extends UnitTestCase
{
    public static function setUpBeforeClass(): void
    {
        self::needsKernel();
    }

    public function testFileConflictExceptionWithDefaultMessage()
    {
        $exception = new FileConflictException();
        $this->assertSame('A file already exists at this path.', $exception->getMessage());
        $this->assertSame(409, $exception->getCode());
    }

    public function testFileConflictExceptionWithPath()
    {
        $exception = new FileConflictException('path/to/file');
        $this->assertSame('File already exists: path/to/file', $exception->getMessage());
        $this->assertSame(409, $exception->getCode());
    }

    public function testFileConflictExceptionWithCustomMessage()
    {
        $exception = new FileConflictException('path/to/file', 'Custom message');
        $this->assertSame('Custom message', $exception->getMessage());
        $this->assertSame(409, $exception->getCode());
    }

    public function testFileNotFoundExceptionWithDefaultMessage()
    {
        $exception = new FileNotFoundException();
        $this->assertSame('File not found.', $exception->getMessage());
        $this->assertSame(404, $exception->getCode());
    }

    public function testFileNotFoundExceptionWithPath()
    {
        $exception = new FileNotFoundException('path/to/file');
        $this->assertSame('File [path/to/file] not found.', $exception->getMessage());
        $this->assertSame(404, $exception->getCode());
    }

    public function testFileNotFoundExceptionWithCustomMessage()
    {
        $exception = new FileNotFoundException('path/to/file', 'Custom message');
        $this->assertSame('Custom message', $exception->getMessage());
        $this->assertSame(404, $exception->getCode());
    }

    public function testRouteNotFoundExceptionWithDefaultMessage()
    {
        $exception = new RouteNotFoundException();
        $this->assertSame('Route not found.', $exception->getMessage());
        $this->assertSame(404, $exception->getCode());
    }

    public function testRouteNotFoundExceptionWithRouteKey()
    {
        $exception = new RouteNotFoundException('route-name');
        $this->assertSame("Route not found: 'route-name'", $exception->getMessage());
        $this->assertSame(404, $exception->getCode());
    }

    public function testRouteNotFoundExceptionWithCustomMessage()
    {
        $exception = new RouteNotFoundException(null, 'Custom message');
        $this->assertSame('Custom message', $exception->getMessage());
        $this->assertSame(404, $exception->getCode());
    }

    public function testUnsupportedPageTypeExceptionWithDefaultMessage()
    {
        $exception = new UnsupportedPageTypeException();
        $this->assertSame('The page type is not supported.', $exception->getMessage());
        $this->assertSame(400, $exception->getCode());
    }

    public function testUnsupportedPageTypeExceptionWithPage()
    {
        $exception = new UnsupportedPageTypeException('some-page');
        $this->assertSame('The page type is not supported: some-page', $exception->getMessage());
        $this->assertSame(400, $exception->getCode());
    }

    public function testBaseUrlNotSetException()
    {
        $exception = new BaseUrlNotSetException();
        $this->assertSame('No site URL has been set in config (or .env).', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
    }
}
