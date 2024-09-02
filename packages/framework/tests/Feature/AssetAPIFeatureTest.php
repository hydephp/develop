<?php

declare(strict_types=1);

use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Support\Facades\Blade;

/**
 * @coversNothing High level test for the Asset API
 */
class AssetAPIFeatureTest extends TestCase
{
    public function testAssetAPIUsagesInBladeViews()
    {
        $view = <<<'Blade'
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

        $version = Hyde::assets()->get('app.css')->getHash();

        $this->assertSame(<<<HTML
        <link rel="stylesheet" href="media/app.css?v=$version">
            <link rel="stylesheet" href="media/app.css">
            <link rel="stylesheet" href="media/app.css">
            <link rel="stylesheet" href="media/app.css">
        
            Missing missing.png

        HTML, $html);
    }
}
