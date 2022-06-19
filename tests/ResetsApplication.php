<?php

namespace Hyde\Testing;

use Hyde\Framework\Actions\CreatesDefaultDirectories;
use Hyde\Framework\Hyde;
use Illuminate\Support\Facades\File;

/**
 * @internal
 */
trait ResetsApplication
{
    /**
     * @deprecated You almost never need to reset everything. Use more granular methods instead.
     */
    public function resetApplication()
    {
        $this->resetMedia();
        $this->resetPages();
        $this->resetPosts();
        $this->resetDocs();
        $this->resetSite();
    }

    public function resetMedia()
    {
        File::cleanDirectory(Hyde::path('_media'));
    }

    public function resetPages()
    {
        File::cleanDirectory(Hyde::path('_pages'));
        Hyde::copy(Hyde::vendorPath('resources/views/homepages/welcome.blade.php'), Hyde::path('_pages/index.blade.php'));
        Hyde::copy(Hyde::vendorPath('resources/views/pages/404.blade.php'), Hyde::path('_pages/404.blade.php'));
    }

    public function resetPosts()
    {
        File::cleanDirectory(Hyde::path('_posts'));
    }

    public function resetDocs()
    {
        File::cleanDirectory(Hyde::path('_docs'));
    }

    public function resetSite()
    {
        File::cleanDirectory(Hyde::path('_site'));
    }
}
