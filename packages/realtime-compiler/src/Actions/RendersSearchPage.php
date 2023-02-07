<?php

namespace Hyde\RealtimeCompiler\Actions;

use Hyde\Hyde;
use Hyde\RealtimeCompiler\Concerns\InteractsWithLaravel;
use Illuminate\Support\Facades\Blade;

class RendersSearchPage
{
    use InteractsWithLaravel;

    public function __invoke(): string
    {
        $this->bootIfNotBooted();

        return Blade::render(file_get_contents(Hyde::vendorPath('resources/views/pages/documentation-search.blade.php')));
    }

    /**
     * @internal
     * @codeCoverageIgnore
     */
    private function bootIfNotBooted(): void
    {
        try {
            Hyde::getInstance();
        } catch (\Throwable) {
            $this->bootApplication();
        }
    }
}
