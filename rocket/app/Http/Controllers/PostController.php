<?php

namespace Hyde\Rocket\Http\Controllers;

use Hyde\Framework\Models\Parsers\MarkdownPostParser;
use Hyde\Rocket\Models\Hyde;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function show(string $slug, Request $request)
    {
        $path = Hyde::path($localPath = '_posts/' . $slug . '.md');

        if (! file_exists($path)) {
            return response('File not found.', 404);
        }

        return view('post-manager', [
            'post' => (new MarkdownPostParser($slug))->get(),
            'slug' => $slug,
            'path' => $path,
            'localPath' => $localPath,
        ]);
    }

    public function store(string $slug, Request $request)
    {
        $path = Hyde::path('_posts/' . $slug . '.md');

        if (! file_exists($path)) {
            return response('File not found.', 404);
        }

        file_put_contents($path, $request->get('markdown'));

        return redirect('/_posts/' . $slug . '?saved=true');
    }
}
