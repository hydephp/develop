<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Foundation;

use Hyde\Foundation\HydeKernel;
use Hyde\Foundation\Facades\Routes;
use Hyde\Foundation\Kernel\Hyperlinks;
use Hyde\Framework\Exceptions\FileNotFoundException;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Foundation\Kernel\Hyperlinks
 */
class HyperlinksTest extends TestCase
{
    protected Hyperlinks $class;

    protected function setUp(): void
    {
        parent::setUp();

        $this->class = new Hyperlinks(HydeKernel::getInstance());
    }

    public function testAssetHelperGetsRelativeWebLinkToImageStoredInSiteMediaFolder()
    {
        $tests = [
            'test.jpg' => 'media/test.jpg',
            'foo' => 'media/foo',
            'http://example.com/test.jpg' => 'http://example.com/test.jpg',
            'https://example.com/test.jpg' => 'https://example.com/test.jpg',
        ];

        foreach ($tests as $input => $expected) {
            $this->assertEquals($this->class->asset($input), $expected);
        }
    }

    public function testAssetHelperResolvesPathsForNestedPages()
    {
        $tests = [
            'test.jpg' => '../media/test.jpg',
            'foo' => '../media/foo',
            'http://example.com/test.jpg' => 'http://example.com/test.jpg',
            'https://example.com/test.jpg' => 'https://example.com/test.jpg',
        ];

        foreach ($tests as $input => $expected) {
            $this->mockCurrentPage('foo/bar');
            $this->assertEquals($this->class->asset($input), $expected);
        }
    }

    public function testAssetHelperReturnsQualifiedAbsoluteUriWhenRequestedAndSiteHasBaseUrl()
    {
        $this->assertEquals('http://localhost/media/test.jpg', $this->class->asset('test.jpg', true));
    }

    public function testAssetHelperReturnsDefaultRelativePathWhenQualifiedAbsoluteUriIsRequestedButSiteHasNoBaseUrl()
    {
        config(['hyde.url' => null]);
        $this->assertEquals('media/test.jpg', $this->class->asset('test.jpg', true));
    }

    public function testAssetHelperReturnsInputWhenQualifiedAbsoluteUriIsRequestedButImageIsAlreadyQualified()
    {
        $this->assertEquals('http://localhost/media/test.jpg', $this->class->asset('http://localhost/media/test.jpg', true));
    }

    public function testAssetHelperUsesConfiguredMediaDirectory()
    {
        Hyde::setMediaDirectory('_assets');
        $this->assertEquals('assets/test.jpg', $this->class->asset('test.jpg'));
    }

    public function testMediaLinkHelper()
    {
        $this->assertSame('media/foo', $this->class->mediaLink('foo'));
    }

    public function testMediaLinkHelperWithRelativePath()
    {
        $this->mockCurrentPage('foo/bar');
        $this->assertSame('../media/foo', $this->class->mediaLink('foo'));
    }

    public function testMediaLinkHelperUsesConfiguredMediaDirectory()
    {
        Hyde::setMediaDirectory('_assets');
        $this->assertSame('assets/foo', $this->class->mediaLink('foo'));
    }

    public function testMediaLinkHelperWithValidationAndExistingFile()
    {
        $this->file('_media/foo');
        $this->assertSame('media/foo', $this->class->mediaLink('foo', true));
    }

    public function testMediaLinkHelperWithValidationAndNonExistingFile()
    {
        $this->expectException(FileNotFoundException::class);
        $this->class->mediaLink('foo', true);
    }

    public function testRouteHelper()
    {
        $this->assertNotNull($this->class->route('index'));
        $this->assertSame(Routes::get('index'), $this->class->route('index'));
    }

    public function testRouteHelperWithInvalidRoute()
    {
        $this->assertNull($this->class->route('foo'));
    }
}
