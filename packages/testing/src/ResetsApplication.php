<?php

namespace Hyde\Testing;

use Hyde\Hyde;

/**
 * @internal
 */
trait ResetsApplication
{
    public function resetApplication(): void
    {
        $this->resetMedia();
        $this->resetPages();
        $this->resetPosts();
        $this->resetDocs();
        $this->resetSite();
    }

    public function resetMedia(): void
    {
        //
    }

    public function resetPages(): void
    {
        array_map('unlinkUnlessDefault', glob(Hyde::path('_pages/*.md')));
        array_map('unlinkUnlessDefault', glob(Hyde::path('_pages/*.blade.php')));
    }

    public function resetPosts(): void
    {
        array_map('unlinkUnlessDefault', glob(Hyde::path('_posts/*.md')));
    }

    public function resetDocs(): void
    {
        array_map('unlinkUnlessDefault', glob(Hyde::path('_docs/*.md')));
    }

    public function resetSite(): void
    {
        array_map('unlinkUnlessDefault', glob(Hyde::path('_site/**/*.html')));
        array_map('unlinkUnlessDefault', glob(Hyde::path('_site/**/*.json')));
        array_map('unlinkUnlessDefault', glob(Hyde::path('_site/*.xml')));
    }

    protected function withoutDefaultPages(): void
    {
        Hyde::unlink('_pages/404.blade.php');
        Hyde::unlink('_pages/index.blade.php');
    }

    public function restoreDefaultPages(): void
    {
        copy(Hyde::vendorPath('resources/views/homepages/welcome.blade.php'), Hyde::path('_pages/index.blade.php'));
        copy(Hyde::vendorPath('resources/views/pages/404.blade.php'), Hyde::path('_pages/404.blade.php'));
    }
}
