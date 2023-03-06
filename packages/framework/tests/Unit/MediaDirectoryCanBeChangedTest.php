<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Facades\Filesystem;
use Hyde\Framework\Services\RebuildService;
use Hyde\Hyde;
use Hyde\Testing\TestCase;

/**
 * High level test of the feature that allows the media source (_media) directory,
 * and the media output directory (_site/media) to be changed.
 *
 * @see \Hyde\Framework\Testing\Unit\BuildOutputDirectoryCanBeChangedTest
 * @see \Hyde\Framework\Testing\Feature\ConfigurableSourceRootsFeatureTest
 */
class MediaDirectoryCanBeChangedTest extends TestCase
{
    public function test_media_output_directory_can_be_changed_for_site_builds()
    {
        Filesystem::deleteDirectory('_site');

        $this->directory('_assets');
        $this->file('_assets/app.css');

        Hyde::setMediaDirectory('_assets');

        $this->artisan('build');

        $this->assertDirectoryDoesNotExist(Hyde::path('_site/media'));
        $this->assertDirectoryExists(Hyde::path('_site/assets'));
        $this->assertFileExists(Hyde::path('_site/assets/app.css'));

        $this->resetSite();
    }

    public function test_media_output_directory_can_be_changed_for_site_rebuilds()
    {
        Filesystem::deleteDirectory('_site');

        $this->directory('_assets');
        $this->file('_assets/app.css');

        Hyde::setMediaDirectory('_assets');

        $this->artisan('rebuild _assets');

        $this->assertDirectoryDoesNotExist(Hyde::path('_site/media'));
        $this->assertDirectoryExists(Hyde::path('_site/assets'));
        $this->assertFileExists(Hyde::path('_site/assets/app.css'));

        $this->resetSite();
    }

    public function test_compiled_pages_have_links_to_the_right_media_file_location()
    {
        Filesystem::moveDirectory('_media', '_assets');
        Hyde::setMediaDirectory('_assets');
        $this->file('_assets/app.js');

        $this->file('_pages/foo.md');
        (new RebuildService('_pages/foo.md'))->execute();

        $this->assertFileExists(Hyde::path('_site/foo.html'));
        $contents = file_get_contents(Hyde::path('_site/foo.html'));
        $this->assertStringContainsString(
            '<link rel="stylesheet" href="assets/app.css?v='.md5_file(Hyde::path('_assets/app.css')).'">',
            $contents
        );

        $this->assertStringContainsString(
            '<script defer src="assets/app.js?v='.md5_file(Hyde::path('_assets/app.js')).'"></script>',
            $contents
        );

        Filesystem::moveDirectory('_assets', '_media');
        Filesystem::delete('_site/foo.html');
        Filesystem::delete('_media/app.js');
    }
}
