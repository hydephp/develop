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

    public function testRunningReturnsTrueWhenViteHotFileExists()
    {
        $this->file('app/storage/framework/cache/vite.hot');

        $this->assertTrue(Vite::running());
    }

    public function testRunningReturnsFalseWhenViteHotFileDoesNotExist()
    {
        $this->assertFalse(Vite::running());
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

    public function testAssetsMethodReturnsHtmlString()
    {
        $this->assertInstanceOf(HtmlString::class, Vite::assets([]));
    }
}
