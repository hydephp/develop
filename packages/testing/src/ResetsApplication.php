<?php

declare(strict_types=1);

namespace Hyde\Testing;

use Hyde\Facades\Filesystem;
use Hyde\Hyde;

trait ResetsApplication
{
    protected function resetApplication(): void
    {
        $this->resetMedia();
        $this->resetPages();
        $this->resetPosts();
        $this->resetDocs();
        $this->resetSite();
    }

    /** @deprecated unless applicable usages are found */
    protected function resetMedia(): void
    {
        //
    }

    protected function resetPages(): void
    {
        array_map('\Hyde\Testing\TestCase::unlinkUnlessDefault', glob(Hyde::path('_pages/*.md')));
        array_map('\Hyde\Testing\TestCase::unlinkUnlessDefault', glob(Hyde::path('_pages/*.blade.php')));
    }

    protected function resetPosts(): void
    {
        array_map('\Hyde\Testing\TestCase::unlinkUnlessDefault', glob(Hyde::path('_posts/*.md')));
    }

    protected function resetDocs(): void
    {
        array_map('\Hyde\Testing\TestCase::unlinkUnlessDefault', glob(Hyde::path('_docs/*.md')));
    }

    protected function resetSite(): void
    {
        Filesystem::cleanDirectory('_site');
    }

    protected function withoutDefaultPages(): void
    {
        Hyde::unlink('_pages/404.blade.php');
        Hyde::unlink('_pages/index.blade.php');
    }

    protected function restoreDefaultPages(): void
    {
        copy(Hyde::vendorPath('resources/views/homepages/welcome.blade.php'), Hyde::path('_pages/index.blade.php'));
        copy(Hyde::vendorPath('resources/views/pages/404.blade.php'), Hyde::path('_pages/404.blade.php'));
    }

    protected static function unlinkUnlessDefault(string $filepath): void
    {
        $protected = [
            'app.css',
            'index.blade.php',
            '404.blade.php',
            '.gitkeep',
        ];

        if (! in_array(basename($filepath), $protected)) {
            unlink($filepath);
        }
    }
}
