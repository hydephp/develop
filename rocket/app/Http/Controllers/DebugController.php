<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Hyde\Framework\Hyde;

class DebugController extends Controller
{
    public function __invoke()
    {
        ob_get_clean();

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

        echo '<h2>HydePHP Debug Information</h2>';
        Project::get('artisan')->passthru('debug');
    }
}
