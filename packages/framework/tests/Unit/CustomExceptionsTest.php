<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Hyde;
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
        $this->assertSame('A file already exists at this path.', (new FileConflictException())->getMessage());
    }

    public function testFileConflictExceptionWithPath()
    {
        $this->assertSame('File [foo] already exists.', (new FileConflictException('foo'))->getMessage());
    }

    public function testFileConflictExceptionWithAbsolutePath()
    {
        $this->assertSame('File [foo] already exists.', (new FileConflictException(Hyde::path('foo')))->getMessage());
    }

    public function testFileNotFoundExceptionWithDefaultMessage()
    {
        $this->assertSame('File not found.', (new FileNotFoundException())->getMessage());
    }

    public function testFileNotFoundExceptionWithPath()
    {
        $this->assertSame('File [foo] not found.', (new FileNotFoundException('foo'))->getMessage());
    }

    public function testFileNotFoundExceptionWithAbsolutePath()
    {
        $this->assertSame('File [foo] not found.', (new FileNotFoundException(Hyde::path('foo')))->getMessage());
    }

    public function testFileNotFoundExceptionWithCustomPath()
    {
        $this->assertSame('foo', (new FileNotFoundException(customMessage: 'foo'))->getMessage());
    }

    public function testRouteNotFoundExceptionWithDefaultMessage()
    {
        $this->assertSame('Route not found.', (new RouteNotFoundException())->getMessage());
    }

    public function testRouteNotFoundExceptionWithRouteKey()
    {
        $this->assertSame('Route [foo] not found.', (new RouteNotFoundException('foo'))->getMessage());
    }

    public function testUnsupportedPageTypeExceptionWithDefaultMessage()
    {
        $this->assertSame('The page type is not supported.', (new UnsupportedPageTypeException())->getMessage());
    }

    public function testUnsupportedPageTypeExceptionWithPage()
    {
        $this->assertSame('The page type [foo] is not supported.', (new UnsupportedPageTypeException('foo'))->getMessage());
    }

    public function testBaseUrlNotSetException()
    {
        $this->assertSame('No site URL has been set in config (or .env).', (new BaseUrlNotSetException())->getMessage());
    }

    public function testFileConflictExceptionCode()
    {
        $this->assertSame(409, (new FileConflictException())->getCode());
    }

    public function testFileNotFoundExceptionCode()
    {
        $this->assertSame(404, (new FileNotFoundException())->getCode());
    }

    public function testRouteNotFoundExceptionCode()
    {
        $this->assertSame(404, (new RouteNotFoundException())->getCode());
    }

    public function testUnsupportedPageTypeExceptionCode()
    {
        $this->assertSame(400, (new UnsupportedPageTypeException())->getCode());
    }

    public function testBaseUrlNotSetExceptionCode()
    {
        $this->assertSame(500, (new BaseUrlNotSetException())->getCode());
    }
}
