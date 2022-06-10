<?php

namespace App\Http\Controllers;

use Hyde\Framework\Hyde;

class DebugController extends Controller
{
    public function __invoke()
    {
        $information = [
            'Hyde/Framework version' => Hyde::version(),
            'Hyde project path' => Hyde::path(),
            'PHP version' => phpversion() . ' (' . PHP_SAPI . ')',
            'Lumen version' => app()->version(),
        ];

        dump($information);
    }
}
