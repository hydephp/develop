<?php

namespace Hyde\RealtimeCompiler\Actions;

use Hyde\Hyde;
use Hyde\RealtimeCompiler\Concerns\InteractsWithLaravel;
use Hyde\Framework\Features\Documentation\DocumentationSearchPage;

class RendersSearchPage
{
    use InteractsWithLaravel;

    public function __invoke(): string
    {
        $this->bootIfNotBooted();

        return file_get_contents(DocumentationSearchPage::generate());
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
