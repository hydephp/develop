<?php

namespace Hyde\Rocket\Http\Controllers;

use GuzzleHttp\Client;

/**
 * Controller for general API requests that don't currently require their own controller.
 */
class ApiController extends Controller
{
    public function pingRealtimeCompiler()
    {
        try {
            $client = new Client();
            $client->head('http://localhost:8080');
            return response()->json(['success' => true]);
        }
        catch (\Throwable) {
            return response()->json([
                'success' => false,
                'error' => 'Could not ping Realtime Compiler on default port 8080'
            ], 500);
        }
    }
}
