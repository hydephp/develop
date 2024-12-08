<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit\Facades;

use Hyde\Testing\UnitTestCase;
use Hyde\Facades\Vite;
use Hyde\Testing\CreatesTemporaryFiles;
use Illuminate\Support\HtmlString;

/**
 * @covers \Hyde\Facades\Vite
 */
class ViteFacadeTest extends UnitTestCase
{
    use CreatesTemporaryFiles;

    protected static bool $needsKernel = true;

    protected function tearDown(): void
    {
        $this->cleanUpFilesystem();
    }

    public function testRunningReturnsTrueWhenEnvironmentVariableIsSet()
    {
        putenv('HYDE_SERVER_VITE=enabled');

        $this->assertTrue(Vite::running());

        putenv('HYDE_SERVER_VITE');
    }

    public function testRunningReturnsFalseWhenEnvironmentVariableIsNotSetOrDisabled()
    {
        $this->assertFalse(Vite::running());

        putenv('HYDE_SERVER_VITE=disabled');

        $this->assertFalse(Vite::running());

        putenv('HYDE_SERVER_VITE');

        $this->assertFalse(Vite::running());
    }

    public function testRunningReturnsTrueWhenViteHotFileExists()
    {
        $this->file('app/storage/framework/cache/vite.hot');

        $this->assertTrue(Vite::running());
    }

    public function testRunningReturnsFalseWhenViteHotFileDoesNotExist()
    {
        $this->assertFileDoesNotExist('app/storage/framework/cache/vite.hot');

        $this->assertFalse(Vite::running());
    }

    public function testItAlwaysImportsClientModule()
    {
        $html = Vite::assets([]);

        $this->assertStringContainsString('<script src="http://localhost:5173/@vite/client" type="module"></script>', (string) $html);

        $html = Vite::assets(['foo.js']);

        $this->assertStringContainsString('<script src="http://localhost:5173/@vite/client" type="module"></script>', (string) $html);
    }

    public function testItDoesNotIncludeUnknownExtensions()
    {
        $this->assertSame((string) Vite::assets([]), (string) Vite::assets(['foo.txt']));
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

    /**
     * @dataProvider cssFileExtensionsProvider
     */
    public function testAssetsMethodSupportsAllCssFileExtensions(string $extension, bool $shouldBeUsed)
    {
        $html = Vite::assets(["resources/css/app.$extension"]);

        $expected = $shouldBeUsed
            ? '<script src="http://localhost:5173/@vite/client" type="module"></script><link rel="stylesheet" href="http://localhost:5173/resources/css/app.'.$extension.'">'
            : '<script src="http://localhost:5173/@vite/client" type="module"></script>';

        $this->assertSame($expected, (string) $html);
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

    public function testAssetMethodDoesNotIncludeUnknownExtensions()
    {
        $html = Vite::asset('unknown.ext');

        $expected = '<script src="http://localhost:5173/@vite/client" type="module"></script>';

        $this->assertSame($expected, (string) $html);
    }

    public static function cssFileExtensionsProvider(): array
    {
        return [
            ['css', true],
            ['less', true],
            ['sass', true],
            ['scss', true],
            ['styl', true],
            ['stylus', true],
            ['pcss', true],
            ['postcss', true],
            ['foo', false],
            ['txt', false],
            ['html', false],
        ];
    }
}
