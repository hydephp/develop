<?php

namespace App\Http\Controllers;

use Hyde\Framework\Hyde;

class DebugController extends Controller
{
    public function __invoke()
    {
        echo '<pre>';

        echo '<h1>Hyde Rocket Debug Screen</h1>';

        echo '<h2>Project Information</h2>';
        $information = [
            'Hyde/Framework version' => Hyde::version(),
            'Hyde project path' => Hyde::path(),
            'PHP version' => phpversion() . ' (' . PHP_SAPI . ')',
            'Lumen version' => app()->version(),
        ];

        dump($information);
    }
}
