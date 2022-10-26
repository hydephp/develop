<?php

namespace Hyde\RealtimeCompiler\Actions;

use Hyde\Hyde;
use Hyde\RealtimeCompiler\Concerns\InteractsWithLaravel;
use Illuminate\Support\Facades\Blade;

class RendersSearchPage
{
    use InteractsWithLaravel;

    public function __invoke()
    {
        $this->bootApplication();

        return Blade::render(file_get_contents(Hyde::vendorPath('resources/views/pages/documentation-search.blade.php')));
    }
}
