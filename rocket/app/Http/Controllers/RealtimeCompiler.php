<?php

namespace Hyde\Rocket\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Routes a request to the realtime compiler.
 *
 * @todo Ping the realtime compiler to see if it's running.
 * @todo Allow host to be specified?
 */
class RealtimeCompiler
{
    public function render(Request $request)
    {
        $path = $request->get('path', '');

        return redirect('http://localhost:8080/' . $path);
    }
}
