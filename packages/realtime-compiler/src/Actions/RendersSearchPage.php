<?php

namespace Hyde\RealtimeCompiler\Actions;

use Hyde\Framework\Hyde;
use Hyde\RealtimeCompiler\Concerns\InteractsWithLaravel;
use Illuminate\Support\Facades\Blade;

/**
 * @deprecated v0.63.x
 */
class RendersSearchPage
{
    use InteractsWithLaravel;

    public function __invoke()
    {
        $this->bootApplication();

        return Blade::render(file_get_contents(Hyde::vendorPath('resources/views/pages/documentation-search.blade.php')));
    }
}
