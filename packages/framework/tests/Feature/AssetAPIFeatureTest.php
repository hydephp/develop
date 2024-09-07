<?php

declare(strict_types=1);

use Hyde\Testing\TestCase;
use Hyde\Foundation\HydeKernel;
use Illuminate\Support\Facades\Blade;
use Hyde\Support\Filesystem\MediaFile;

/**
 * High level test for the Asset API.
 *
 * @covers \Hyde\Facades\Asset
 * @covers \Hyde\Support\Filesystem\MediaFile
 * @covers \Hyde\Foundation\Kernel\Hyperlinks
 */
class AssetAPIFeatureTest extends TestCase
{
    public function testAssetAPIExamples()
    {
        // Tests for the Asset API, can be used to try out the PhpStorm autocompletion

        $conditions = [
            'hasMediaFileTrue' => \Hyde\Facades\Asset::hasMediaFile('app.css'),
            'hasMediaFileFalse' => \Hyde\Facades\Asset::hasMediaFile('missing.png'),
            'getters' => [
                (string) \Hyde\Facades\Asset::get('app.css'),
                \Hyde\Facades\Asset::get('app.css'),
                \Hyde::asset('app.css'),
                \Hyde\Hyde::asset('app.css'),
                Hyde::kernel()->asset('app.css'),
                HydeKernel::getInstance()->asset('app.css'),
                hyde()->asset('app.css'),
                asset('app.css'),
            ],
            'accessors' => [
                new MediaFile('app.css'),
                MediaFile::make('app.css'),
                MediaFile::get('app.css'),
                MediaFile::sourcePath('app.css'),
                MediaFile::outputPath('app.css'),
            ],
        ];

        $this->assertEquals([
            'hasMediaFileTrue' => true,
            'hasMediaFileFalse' => false,
            'getters' => [
                "media/app.css?v={$this->getAppStylesVersion()}",
                MediaFile::get('app.css'),
                MediaFile::get('app.css'),
                MediaFile::get('app.css'),
                MediaFile::get('app.css'),
                MediaFile::get('app.css'),
                MediaFile::get('app.css'),
                MediaFile::get('app.css'),
            ],
            'accessors' => [
                new MediaFile('app.css'),
                new MediaFile('app.css'),
                MediaFile::get('app.css'),
                Hyde::path('_media/app.css'),
                Hyde::sitePath('media/app.css'),
            ],
        ], $conditions);
    }

    public function testAssetAPIUsagesInBladeViews()
    {
        $view = /** @lang Blade */ <<<'Blade'
        @if(Asset::hasMediaFile('app.css'))
            <link rel="stylesheet" href="{{ Asset::get('app.css') }}">
            <link rel="stylesheet" href="{{ Hyde::asset('app.css') }}">
            <link rel="stylesheet" href="{{ asset('app.css') }}">
        @endif
        
        @if(Asset::hasMediaFile('missing.png'))
            Found missing.png
        @else
            Missing missing.png
        @endif
        Blade;

        $html = Blade::render($view);

        $this->assertSame(<<<HTML
        <link rel="stylesheet" href="media/app.css?v={$this->getAppStylesVersion()}">
            <link rel="stylesheet" href="media/app.css?v={$this->getAppStylesVersion()}">
            <link rel="stylesheet" href="media/app.css?v={$this->getAppStylesVersion()}">
        
            Missing missing.png

        HTML, $html);
    }

    protected function getAppStylesVersion(): string
    {
        return MediaFile::get('app.css')->getHash();
    }
}
