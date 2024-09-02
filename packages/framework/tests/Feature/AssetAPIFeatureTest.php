<?php

declare(strict_types=1);

use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Blade;
use Hyde\Support\Filesystem\MediaFile;

/**
 * @coversNothing High level test for the Asset API
 */
class AssetAPIFeatureTest extends TestCase
{
    public function testAssetAPIExamples()
    {
        $conditions = [
            'hasMediaFileTrue' => Asset::hasMediaFile('app.css'),
            'hasMediaFileFalse' => Asset::hasMediaFile('missing.png'),
            'links' => [
                Asset::mediaLink('app.css'),
                Asset::get('app.css'),
                Hyde::asset('app.css'),
                asset('app.css'),
            ],
        ];

        $this->assertSame([
            'hasMediaFileTrue' => true,
            'hasMediaFileFalse' => false,
            'links' => [
                "media/app.css?v={$this->getAppStylesVersion()}",
                MediaFile::get('app.css'),
                MediaFile::get('app.css'),
                MediaFile::get('app.css'),
            ],
        ], $conditions);
    }

    public function testAssetAPIUsagesInBladeViews()
    {
        $view = /** @lang Blade */ <<<'Blade'
        @if(Asset::hasMediaFile('app.css'))
            <link rel="stylesheet" href="{{ Asset::mediaLink('app.css') }}">
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
            <link rel="stylesheet" href="media/app.css">
            <link rel="stylesheet" href="media/app.css">
            <link rel="stylesheet" href="media/app.css">
        
            Missing missing.png

        HTML, $html);
    }

    protected function getAppStylesVersion(): string
    {
        return MediaFile::get('app.css')->getHash();
    }
}
