<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Facades;

use Hyde\Testing\UnitTestCase;
use Hyde\Facades\Vite;
use Hyde\Testing\CreatesTemporaryFiles;
use Illuminate\Support\HtmlString;
use InvalidArgumentException;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Facades\Vite::class)]
class ViteFacadeTest extends UnitTestCase
{
    use CreatesTemporaryFiles;

    protected static bool $needsKernel = true;

    protected function tearDown(): void
    {
        $this->cleanUpFilesystem();
    }

    public function testRunningReturnsTrueWhenViteHotFileExists()
    {
        $this->file('app/storage/framework/runtime/vite.hot');

        $this->assertTrue(Vite::running());
    }

    public function testRunningReturnsFalseWhenViteHotFileDoesNotExist()
    {
        $this->assertFileDoesNotExist('app/storage/framework/runtime/vite.hot');

        $this->assertFalse(Vite::running());
    }

    public function testItAlwaysImportsClientModule()
    {
        $html = Vite::assets([]);

        $this->assertStringContainsString('<script src="http://localhost:5173/@vite/client" type="module"></script>', (string) $html);

        $html = Vite::assets(['foo.js']);

        $this->assertStringContainsString('<script src="http://localhost:5173/@vite/client" type="module"></script>', (string) $html);
    }

    public function testAssetMethodThrowsExceptionForUnknownExtensions()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Unsupported asset type for path: 'foo.txt'");

        Vite::asset('foo.txt');
    }

    public function testAssetsMethodThrowsExceptionForUnknownExtensions()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Unsupported asset type for path: 'foo.txt'");

        Vite::assets(['foo.txt']);
    }

    public function testAssetsMethodReturnsHtmlString()
    {
        $this->assertInstanceOf(HtmlString::class, Vite::assets([]));
        $this->assertInstanceOf(HtmlString::class, Vite::assets(['foo.js']));

        $this->assertEquals(new HtmlString('<script src="http://localhost:5173/@vite/client" type="module"></script>'), Vite::assets([]));
        $this->assertEquals(new HtmlString('<script src="http://localhost:5173/@vite/client" type="module"></script><script src="http://localhost:5173/foo.js" type="module"></script>'), Vite::assets(['foo.js']));
    }

    public function testAssetsMethodGeneratesCorrectHtmlForJavaScriptFiles()
    {
        $html = Vite::assets(['resources/js/app.js']);

        $expected = '<script src="http://localhost:5173/@vite/client" type="module"></script><script src="http://localhost:5173/resources/js/app.js" type="module"></script>';

        $this->assertSame($expected, (string) $html);
    }

    public function testAssetsMethodGeneratesCorrectHtmlForCssFiles()
    {
        $html = Vite::assets(['resources/css/app.css']);

        $expected = '<script src="http://localhost:5173/@vite/client" type="module"></script><link rel="stylesheet" href="http://localhost:5173/resources/css/app.css">';

        $this->assertSame($expected, (string) $html);
    }

    public function testAssetsMethodGeneratesCorrectHtmlForMultipleFiles()
    {
        $html = Vite::assets([
            'resources/js/app.js',
            'resources/css/app.css',
            'resources/js/other.js',
        ]);

        $expected = '<script src="http://localhost:5173/@vite/client" type="module"></script><script src="http://localhost:5173/resources/js/app.js" type="module"></script><link rel="stylesheet" href="http://localhost:5173/resources/css/app.css"><script src="http://localhost:5173/resources/js/other.js" type="module"></script>';

        $this->assertSame($expected, (string) $html);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('cssFileExtensionsProvider')]
    public function testAssetsMethodSupportsAllCssFileExtensions(string $extension)
    {
        $html = Vite::assets(["resources/css/app.$extension"]);

        if ($extension !== 'js') {
            $expected = '<script src="http://localhost:5173/@vite/client" type="module"></script><link rel="stylesheet" href="http://localhost:5173/resources/css/app.'.$extension.'">';

            $this->assertStringContainsString('stylesheet', (string) $html);
            $this->assertSame($expected, (string) $html);
        } else {
            $this->assertStringNotContainsString('stylesheet', (string) $html);
        }
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('jsFileExtensionsProvider')]
    public function testAssetsMethodSupportsAllJsFileExtensions(string $extension)
    {
        $html = Vite::assets(["resources/js/app.$extension"]);

        if ($extension !== 'css') {
            $expected = '<script src="http://localhost:5173/@vite/client" type="module"></script><script src="http://localhost:5173/resources/js/app.'.$extension.'" type="module"></script>';

            $this->assertStringNotContainsString('stylesheet', (string) $html);
            $this->assertSame($expected, (string) $html);
        } else {
            $this->assertStringContainsString('stylesheet', (string) $html);
        }
    }

    public function testAssetMethodReturnsHtmlString()
    {
        $this->assertInstanceOf(HtmlString::class, Vite::asset('foo.js'));
    }

    public function testAssetMethodGeneratesCorrectHtmlForJavaScriptFile()
    {
        $html = Vite::asset('resources/js/app.js');

        $expected = '<script src="http://localhost:5173/@vite/client" type="module"></script><script src="http://localhost:5173/resources/js/app.js" type="module"></script>';

        $this->assertSame($expected, (string) $html);
    }

    public function testAssetMethodGeneratesCorrectHtmlForCssFile()
    {
        $html = Vite::asset('resources/css/app.css');

        $expected = '<script src="http://localhost:5173/@vite/client" type="module"></script><link rel="stylesheet" href="http://localhost:5173/resources/css/app.css">';

        $this->assertSame($expected, (string) $html);
    }

    public static function cssFileExtensionsProvider(): \Iterator
    {
        yield ['css'];
        yield ['less'];
        yield ['sass'];
        yield ['scss'];
        yield ['styl'];
        yield ['stylus'];
        yield ['pcss'];
        yield ['postcss'];
        yield ['js'];
    }

    public static function jsFileExtensionsProvider(): \Iterator
    {
        yield ['js'];
        yield ['jsx'];
        yield ['ts'];
        yield ['tsx'];
        yield ['css'];
    }
}
